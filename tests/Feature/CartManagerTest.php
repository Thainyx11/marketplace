<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\CartManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_add_clamps_quantity_to_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 3]);

        $manager = new CartManager($user);
        $manager->add($product, 10);

        $this->assertSame(3, $manager->count());
    }

    public function test_authenticated_add_accumulates_across_calls_up_to_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $manager = new CartManager($user);
        $manager->add($product, 2);
        $manager->add($product, 2);

        $this->assertSame(4, $manager->count());
    }

    public function test_authenticated_update_quantity_to_zero_removes_the_item(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $manager = new CartManager($user);
        $manager->add($product, 2);
        $manager->updateQuantity($product, 0);

        $this->assertSame(0, $manager->count());
    }

    public function test_authenticated_remove_and_clear(): void
    {
        $user = User::factory()->create();
        $productA = Product::factory()->create(['stock' => 5]);
        $productB = Product::factory()->create(['stock' => 5]);

        $manager = new CartManager($user);
        $manager->add($productA, 1);
        $manager->add($productB, 1);

        $manager->remove($productA);
        $this->assertSame(1, $manager->count());

        $manager->clear();
        $this->assertSame(0, $manager->count());
    }

    public function test_guest_cart_lives_in_session_and_clamps_to_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $manager = new CartManager(null);
        $manager->add($product, 10);

        $this->assertSame(2, $manager->count());
        $this->assertSame(2, session('guest_cart')[$product->id]);
    }

    public function test_merge_session_into_db_transfers_guest_cart_on_login(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        // Simulate items added while browsing as a guest.
        session(['guest_cart' => [$product->id => 3]]);

        CartManager::mergeSessionIntoDb($user);

        $manager = new CartManager($user);
        $this->assertSame(3, $manager->count());
        $this->assertSame([], session('guest_cart', []));
    }
}
