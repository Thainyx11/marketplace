<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use App\Services\CartManager;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

/**
 * Regression tests for the same root-cause bug found in three more places
 * beyond the admin back-office (see AdminOrderOverrideTest and the extended
 * OrderVisibilityTest): a Livewire action calling session()->flash(...)
 * with no redirect afterwards never actually shows the message —
 * <x-flash-messages> lives in the shared layout, outside any single
 * component's own AJAX re-render boundary. Each of these now also
 * dispatches a "flash-message" browser event, which
 * resources/views/components/flash-messages.blade.php picks up via
 * Livewire.on() regardless of which component fired it.
 */
class FlashMessageDeliveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_checking_out_an_empty_cart_flashes_a_visible_error(): void
    {
        // 'role' explicitly passed: the users.role column defaults to
        // 'acheteur' at the DB level, but that default never backfills onto
        // the in-memory model create() returns, and actingAs() uses that
        // exact (stale) instance as the authenticated user.
        $buyer = User::factory()->create(['role' => 'acheteur', 'is_active' => true]);

        Volt::actingAs($buyer)->test('pages.cart.show')
            ->call('checkout')
            ->assertDispatched('flash-message', message: __('Votre panier est vide.'), type: 'error');
    }

    public function test_a_non_buyer_checking_out_flashes_a_visible_error(): void
    {
        // Reachable via a guest cart merged into a non-buyer account at login
        // (CartManager::mergeSessionIntoDb() does not check role), even
        // though AddToCart itself blocks a *logged-in* non-buyer from
        // adding to their own cart directly.
        $vendor = User::factory()->vendeur()->create(['is_active' => true]);
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();

        // Bypass AddToCart's own role gate by writing the cart line directly,
        // matching how a merged guest cart would arrive for this account.
        (new CartManager($vendor))->add($product, 1);

        Volt::actingAs($vendor)->test('pages.cart.show')
            ->call('checkout')
            ->assertDispatched('flash-message', message: __('Seuls les comptes acheteur peuvent passer commande.'), type: 'error');
    }

    public function test_reporting_a_message_flashes_a_visible_confirmation(): void
    {
        $seller = User::factory()->vendeur()->create(['is_active' => true]);
        $buyer = User::factory()->create(['is_active' => true]);
        $category = Category::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for($category)->create();

        $message = Message::create([
            'sender_id' => $buyer->id,
            'receiver_id' => $seller->id,
            'product_id' => $product->id,
            'content' => 'Bonjour, disponible ?',
        ]);

        Volt::actingAs($seller)->test('pages.messages.show', ['product' => $product, 'user' => $buyer])
            ->call('report', $message->id)
            ->assertDispatched('flash-message', message: __('Message signalé aux administrateurs.'), type: 'status');

        $this->assertDatabaseHas('message_reports', [
            'message_id' => $message->id,
            'reported_by' => $seller->id,
        ]);
    }
}
