<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\PromoCode;
use App\Services\CartManager;
use Illuminate\Support\Facades\Redirect;
use Stripe\Checkout\Session;
use Stripe\Coupon;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public const EXPRESS_FEE = 4.90;

    public function show()
    {
        $manager = new CartManager(auth()->user());
        $items = $manager->items();

        abort_if($items->isEmpty(), 404);

        return view('checkout.show', [
            'items' => $items,
            'total' => $manager->total(),
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $manager = new CartManager($request->user());
        $items = $manager->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.show')->with('error', __('Votre panier est vide.'));
        }

        foreach ($items as $entry) {
            if ($entry['quantity'] > $entry['product']->stock) {
                return redirect()->route('cart.show')
                    ->with('error', __(':title n\'a plus assez de stock disponible.', ['title' => $entry['product']->title]));
            }
        }

        $subtotal = $manager->total();
        $discount = 0;
        $promoCode = null;

        if ($request->filled('promo_code')) {
            $promoCode = PromoCode::where('code', $request->string('promo_code'))->first();

            if (! $promoCode || ! $promoCode->isValid()) {
                return back()->withErrors(['promo_code' => __('Ce code promo est invalide ou expiré.')])->withInput();
            }

            $discount = $promoCode->discountFor($subtotal);
        }

        if (! config('services.stripe.secret')) {
            return back()->withErrors(['promo_code' => __("Le paiement en ligne n'est pas encore configuré (clé Stripe manquante côté serveur).")])->withInput();
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = $items->map(fn (array $entry) => [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $entry['product']->title],
                'unit_amount' => (int) round($entry['product']->price * 100),
            ],
            'quantity' => $entry['quantity'],
        ])->values()->all();

        $shippingMethod = $request->string('shipping_method')->value();

        if ($shippingMethod === 'express') {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => __('Livraison express')],
                    'unit_amount' => (int) round(self::EXPRESS_FEE * 100),
                ],
                'quantity' => 1,
            ];
        }

        $sessionParams = [
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('cart.show'),
            'customer_email' => $request->user()->email,
            'metadata' => [
                'buyer_id' => $request->user()->id,
                'shipping_address' => $request->string('shipping_address'),
                'shipping_method' => $shippingMethod,
                'promo_code_id' => $promoCode?->id ?? '',
                'discount_amount' => (string) $discount,
            ],
        ];

        if ($discount > 0) {
            try {
                $coupon = Coupon::create([
                    'amount_off' => (int) round($discount * 100),
                    'currency' => 'eur',
                    'duration' => 'once',
                    'name' => 'Code promo '.$promoCode->code,
                ]);

                $sessionParams['discounts'] = [['coupon' => $coupon->id]];
            } catch (ApiErrorException $e) {
                report($e);

                return back()->withErrors(['promo_code' => __("Impossible d'appliquer le code promo pour le moment.")])->withInput();
            }
        }

        try {
            $session = Session::create($sessionParams);
        } catch (ApiErrorException $e) {
            report($e);

            return back()->withErrors(['promo_code' => __('Le paiement a échoué : :error', ['error' => $e->getMessage()])])->withInput();
        }

        return Redirect::away($session->url);
    }

    public function success()
    {
        return view('checkout.success');
    }
}
