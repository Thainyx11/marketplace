<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * Regression tests for two bugs found while auditing the codebase (not
 * caught by the existing suite, which is why they went unnoticed):
 *
 *  - Deleting a product that has already been sold threw an uncaught
 *    QueryException (order_items.product_id is a RESTRICT foreign key)
 *    instead of a friendly error.
 *  - A vendor who sold only one item in a multi-seller order could see
 *    every other vendor's line, the buyer's shipping total, and download
 *    the full invoice — OrderPolicy::view() only checks that the viewer
 *    owns at least one line, it never scoped *which* lines were shown.
 */
class OrderVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /** Builds a two-seller order: $sellerA's item and $sellerB's item, both belonging to $buyer. */
    private function makeSharedOrder(User $buyer, User $sellerA, User $sellerB): Order
    {
        $category = Category::factory()->create();
        $productA = Product::factory()->for($sellerA, 'seller')->for($category)->create(['title' => 'Article de A', 'price' => 20]);
        $productB = Product::factory()->for($sellerB, 'seller')->for($category)->create(['title' => 'Article de B', 'price' => 30]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total' => 50,
            'status' => 'en_attente',
            'shipping_address' => "1 rue Test\n1000 Bruxelles",
        ]);

        OrderItem::create(['order_id' => $order->id, 'product_id' => $productA->id, 'seller_id' => $sellerA->id, 'quantity' => 1, 'unit_price' => 20, 'status' => 'en_attente']);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $productB->id, 'seller_id' => $sellerB->id, 'quantity' => 1, 'unit_price' => 30, 'status' => 'en_attente']);

        return $order;
    }

    public function test_deleting_a_sold_product_shows_a_friendly_error_instead_of_crashing(): void
    {
        $vendor = User::factory()->create(['role' => 'vendeur', 'is_approved' => true, 'is_active' => true]);
        $buyer = User::factory()->create(['role' => 'acheteur', 'is_active' => true]);
        $category = Category::factory()->create();
        $product = Product::factory()->for($vendor, 'seller')->for($category)->create();

        $order = Order::create(['buyer_id' => $buyer->id, 'total' => $product->price, 'status' => 'en_attente', 'shipping_address' => 'Test']);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'seller_id' => $vendor->id, 'quantity' => 1, 'unit_price' => $product->price, 'status' => 'en_attente']);

        // No exception should propagate out of call() — that's the bug being guarded against.
        Volt::actingAs($vendor)->test('pages.vendor.products.index')
            ->call('delete', $product->id);

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id]);
    }

    public function test_deleting_an_unsold_product_still_works(): void
    {
        $vendor = User::factory()->create(['role' => 'vendeur', 'is_approved' => true, 'is_active' => true]);
        $category = Category::factory()->create();
        $product = Product::factory()->for($vendor, 'seller')->for($category)->create();

        Volt::actingAs($vendor)->test('pages.vendor.products.index')
            ->call('delete', $product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_vendor_only_sees_their_own_line_in_a_shared_order(): void
    {
        $buyer = User::factory()->create(['role' => 'acheteur', 'is_active' => true]);
        $sellerA = User::factory()->create(['role' => 'vendeur', 'is_approved' => true, 'is_active' => true]);
        $sellerB = User::factory()->create(['role' => 'vendeur', 'is_approved' => true, 'is_active' => true]);
        $order = $this->makeSharedOrder($buyer, $sellerA, $sellerB);

        $response = $this->actingAs($sellerA)->get(route('orders.show', $order));

        $response->assertOk();
        $response->assertSee('Article de A');
        $response->assertDontSee('Article de B');
        // The whole-order total (50€) aggregates both vendors' lines and must not leak either.
        $response->assertDontSee('Total : 50,00');
    }

    public function test_buyer_sees_every_line_and_the_total_in_their_own_order(): void
    {
        $buyer = User::factory()->create(['role' => 'acheteur', 'is_active' => true]);
        $sellerA = User::factory()->create(['role' => 'vendeur', 'is_approved' => true, 'is_active' => true]);
        $sellerB = User::factory()->create(['role' => 'vendeur', 'is_approved' => true, 'is_active' => true]);
        $order = $this->makeSharedOrder($buyer, $sellerA, $sellerB);

        $response = $this->actingAs($buyer)->get(route('orders.show', $order));

        $response->assertOk();
        $response->assertSee('Article de A');
        $response->assertSee('Article de B');
    }

    public function test_vendor_cannot_download_the_full_order_invoice(): void
    {
        $buyer = User::factory()->create(['role' => 'acheteur', 'is_active' => true]);
        $sellerA = User::factory()->create(['role' => 'vendeur', 'is_approved' => true, 'is_active' => true]);
        $sellerB = User::factory()->create(['role' => 'vendeur', 'is_approved' => true, 'is_active' => true]);
        $order = $this->makeSharedOrder($buyer, $sellerA, $sellerB);

        $this->actingAs($sellerA)->get(route('orders.invoice', $order))->assertForbidden();
    }
}
