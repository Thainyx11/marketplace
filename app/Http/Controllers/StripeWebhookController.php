<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = $secret
                ? Webhook::constructEvent($request->getContent(), $request->header('Stripe-Signature', ''), $secret)
                : json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (SignatureVerificationException|\JsonException $e) {
            Log::warning('Stripe webhook rejected: '.$e->getMessage());

            return response('Invalid signature', 400);
        }

        if (($event->type ?? null) === 'checkout.session.completed') {
            $this->fulfillOrder($event->data->object);
        }

        return response('ok', 200);
    }

    private function fulfillOrder(object $session): void
    {
        // Already processed (Stripe retries webhooks that don't 200 fast enough).
        if (Payment::where('stripe_id', $session->id)->exists()) {
            return;
        }

        $metadata = $session->metadata;
        $buyer = User::find($metadata->buyer_id ?? null);

        if (! $buyer) {
            Log::error("Stripe webhook: buyer not found for session {$session->id}");

            return;
        }

        $manager = new CartManager($buyer);
        $items = $manager->items();

        if ($items->isEmpty()) {
            Log::error("Stripe webhook: cart empty at fulfillment for session {$session->id}");

            return;
        }

        $discount = (float) ($metadata->discount_amount ?? 0);
        $shippingMethod = ($metadata->shipping_method ?? 'standard') === 'express' ? 'express' : 'standard';
        $shippingFee = $shippingMethod === 'express' ? CheckoutController::EXPRESS_FEE : 0;
        $subtotal = $manager->total();
        $total = max(0, $subtotal - $discount + $shippingFee);
        $commissionRate = (float) Setting::get('commission_rate', 5) / 100;

        DB::transaction(function () use ($items, $buyer, $metadata, $discount, $total, $session, $commissionRate, $shippingMethod) {
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'promo_code_id' => $metadata->promo_code_id ?: null,
                'discount_amount' => $discount,
                'total' => $total,
                'status' => 'en_attente',
                'shipping_address' => $metadata->shipping_address ?? $buyer->shipping_address ?? '',
                'shipping_method' => $shippingMethod,
            ]);

            foreach ($items as $entry) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $entry['product']->id,
                    'seller_id' => $entry['product']->user_id,
                    'quantity' => $entry['quantity'],
                    'unit_price' => $entry['product']->price,
                    'status' => 'en_attente',
                ]);

                $entry['product']->decrement('stock', $entry['quantity']);
            }

            Payment::create([
                'order_id' => $order->id,
                'stripe_id' => $session->id,
                'amount' => $total,
                'commission' => round($total * $commissionRate, 2),
                'status' => 'paid',
            ]);

            if ($metadata->promo_code_id ?? null) {
                PromoCode::where('id', $metadata->promo_code_id)->increment('used_count');
            }

            $manager->clear();
        });
    }
}
