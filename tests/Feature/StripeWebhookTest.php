<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for two real bugs found and fixed via manual end-to-end
 * testing: the webhook trusting an unsigned payload when no secret was
 * configured, and $manager being missing from the DB::transaction() closure's
 * use() list (payment succeeded on Stripe's side but no order was ever created).
 */
class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_when_secret_not_configured(): void
    {
        config(['services.stripe.webhook_secret' => null]);

        $response = $this->postJson('/webhook/stripe', ['type' => 'checkout.session.completed']);

        $response->assertStatus(503);
        $this->assertSame(0, Payment::count());
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

        $response = $this->call(
            'POST',
            '/webhook/stripe',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => 't=123,v1=not-a-real-signature'],
            json_encode(['type' => 'checkout.session.completed'])
        );

        $response->assertStatus(400);
        $this->assertSame(0, Payment::count());
    }

    public function test_valid_webhook_creates_order_and_clears_cart(): void
    {
        $secret = 'whsec_test_secret';
        config(['services.stripe.webhook_secret' => $secret]);

        $buyer = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5, 'price' => 19.50]);

        $cart = Cart::create(['user_id' => $buyer->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        $payload = json_encode([
            'id' => 'evt_test_1',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_session_1',
                    'metadata' => [
                        'buyer_id' => (string) $buyer->id,
                        'discount_amount' => '0',
                        'shipping_method' => 'standard',
                        'shipping_address' => '12 rue des Cartes, 1000 Bruxelles',
                        'promo_code_id' => '',
                    ],
                ],
            ],
        ]);

        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);
        $header = "t={$timestamp},v1={$signature}";

        $response = $this->call(
            'POST',
            '/webhook/stripe',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => $header, 'CONTENT_TYPE' => 'application/json'],
            $payload
        );

        $response->assertStatus(200);

        $this->assertSame(1, Order::count());
        $this->assertSame(1, Payment::where('stripe_id', 'cs_test_session_1')->count());

        $product->refresh();
        $this->assertSame(4, $product->stock);

        $this->assertSame(0, $cart->items()->count());
    }
}
