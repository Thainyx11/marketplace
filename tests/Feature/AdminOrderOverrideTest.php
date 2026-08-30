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
 * Regression tests for two bugs found while auditing the admin "litige"
 * (dispute) tool on an order's detail page:
 *
 *  - overrideStatus() updated only orders.status, never order_items.status.
 *    Order::recomputeStatus() derives orders.status FROM order_items.status
 *    everywhere else in the app, never the reverse, so the two silently
 *    diverged: confirmed live by forcing an order to "livree" and seeing the
 *    buyer's order list show "Livrée" while that same order's detail page
 *    (which reads order_items.status) still showed "En attente" — and the
 *    override would later be reverted without warning the next time any
 *    vendor on the order updated their own line, since recomputeStatus()
 *    would recompute from the untouched order_items rows.
 *
 *  - session()->flash('status', ...) inside a Livewire action never
 *    actually reached the user: <x-flash-messages> lives in the shared
 *    layout, outside any single Livewire component's own AJAX re-render
 *    boundary, so nothing ever displayed it without a full page reload.
 *    Confirmed live: after clicking "Forcer ce statut", the flashed text
 *    was nowhere in the DOM. Fixed by also dispatching a "flash-message"
 *    browser event that a page-level script listens for via Livewire.on().
 */
class AdminOrderOverrideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_forcing_an_order_status_also_forces_every_line_item_to_match(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $buyer = User::factory()->create(['is_active' => true]);
        $sellerA = User::factory()->vendeur()->create(['is_active' => true]);
        $sellerB = User::factory()->vendeur()->create(['is_active' => true]);
        $category = Category::factory()->create();
        $productA = Product::factory()->for($sellerA, 'seller')->for($category)->create(['price' => 20]);
        $productB = Product::factory()->for($sellerB, 'seller')->for($category)->create(['price' => 30]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'total' => 50,
            'status' => 'en_attente',
            'shipping_address' => "1 rue Test\n1000 Bruxelles",
        ]);
        $itemA = OrderItem::create(['order_id' => $order->id, 'product_id' => $productA->id, 'seller_id' => $sellerA->id, 'quantity' => 1, 'unit_price' => 20, 'status' => 'en_attente']);
        $itemB = OrderItem::create(['order_id' => $order->id, 'product_id' => $productB->id, 'seller_id' => $sellerB->id, 'quantity' => 1, 'unit_price' => 30, 'status' => 'en_attente']);

        Volt::actingAs($admin)->test('pages.admin.orders.show', ['order' => $order])
            ->set('status', 'livree')
            ->call('overrideStatus')
            ->assertDispatched('flash-message', message: 'Statut de la commande mis à jour (intervention manuelle).', type: 'status');

        $this->assertSame('livree', $order->fresh()->status);
        $this->assertSame('livree', $itemA->fresh()->status);
        $this->assertSame('livree', $itemB->fresh()->status);
    }

    public function test_a_non_admin_cannot_override_an_order_status(): void
    {
        $buyer = User::factory()->create(['is_active' => true]);
        $seller = User::factory()->vendeur()->create(['is_active' => true]);
        $category = Category::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for($category)->create(['price' => 20]);

        $order = Order::create(['buyer_id' => $buyer->id, 'total' => 20, 'status' => 'en_attente', 'shipping_address' => 'Test']);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'seller_id' => $seller->id, 'quantity' => 1, 'unit_price' => 20, 'status' => 'en_attente']);

        $this->assertFalse($buyer->can('update', $order));
    }
}
