<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\CartManager;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression guard for a bug found while manually testing the deployed
 * checkout page: picking "Express" shipping, then submitting with an
 * invalid promo code, silently reverted the visible selection back to
 * "Standard" — even though the controller does flash the old input back
 * (return back()->withErrors(...)->withInput()). The address and promo
 * fields both correctly read old(), but the Alpine x-data initializer for
 * shippingMethod was a hardcoded 'standard' literal that never looked at
 * old('shipping_method') at all.
 */
class CheckoutShippingMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_promo_code_keeps_the_chosen_shipping_method_selected(): void
    {
        $this->seed(RoleSeeder::class);
        $buyer = User::factory()->create(['role' => 'acheteur']);
        $product = Product::factory()->create(['stock' => 5]);

        (new CartManager($buyer))->add($product, 1);

        $response = $this->actingAs($buyer)->from(route('checkout.show'))->post(route('checkout.store'), [
            'shipping_address' => '1 rue de Test, 1000 Bruxelles',
            'shipping_method' => 'express',
            'promo_code' => 'CODE-INVALIDE',
        ]);

        $response->assertRedirect(route('checkout.show'));
        $response->assertSessionHasErrors('promo_code');
        $this->assertSame('express', old('shipping_method'));

        // Follow the redirect and check the page actually re-renders the
        // Alpine initializer with the preserved value, not just that the
        // session carries it — this is exactly what silently regressed.
        $page = $this->actingAs($buyer)->get(route('checkout.show'));
        $page->assertSee("shippingMethod: 'express'", false);
    }
}
