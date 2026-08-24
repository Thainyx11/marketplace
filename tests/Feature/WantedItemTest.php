<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\WantedItem;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class WantedItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_guest_can_view_index_and_show_but_not_create(): void
    {
        $item = WantedItem::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Charizard VMAX',
            'status' => 'ouverte',
        ]);

        $this->get('/recherches')->assertOk();
        $this->get("/recherches/{$item->id}")->assertOk();
        $this->get('/recherches/nouvelle')->assertRedirect('/login');
    }

    public function test_active_buyer_can_create_wanted_item(): void
    {
        $buyer = User::factory()->create(['role' => 'acheteur', 'is_active' => true]);

        Volt::actingAs($buyer)
            ->test('pages.wanted.create')
            ->set('title', 'Charizard VMAX')
            ->set('description', 'État neuf uniquement')
            ->call('save');

        $this->assertDatabaseHas('wanted_items', [
            'user_id' => $buyer->id,
            'title' => 'Charizard VMAX',
            'status' => 'ouverte',
        ]);
    }

    public function test_pending_vendor_cannot_respond(): void
    {
        $vendor = User::factory()->create(['role' => 'vendeur', 'is_approved' => false]);
        $item = WantedItem::create(['user_id' => User::factory()->create()->id, 'title' => 'x', 'status' => 'ouverte']);

        $this->assertFalse($vendor->can('respond', $item));
    }

    public function test_approved_vendor_can_respond(): void
    {
        $vendor = User::factory()->vendeur()->create();
        $item = WantedItem::create(['user_id' => User::factory()->create()->id, 'title' => 'Charizard VMAX', 'status' => 'ouverte']);

        $this->assertTrue($vendor->can('respond', $item));

        Volt::actingAs($vendor)
            ->test('pages.wanted.show', ['wantedItem' => $item])
            ->set('content', "J'ai cet article en stock.")
            ->call('respond');

        $this->assertDatabaseHas('wanted_item_responses', [
            'wanted_item_id' => $item->id,
            'seller_id' => $vendor->id,
        ]);
    }

    public function test_only_author_can_mark_fulfilled(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $item = WantedItem::create(['user_id' => $author->id, 'title' => 'x', 'status' => 'ouverte']);

        $this->assertFalse($other->can('update', $item));
        $this->assertTrue($author->can('update', $item));

        Volt::actingAs($author)
            ->test('pages.wanted.show', ['wantedItem' => $item])
            ->call('markFulfilled');

        $this->assertSame('pourvue', $item->fresh()->status);
    }

    public function test_response_with_linked_product_renders(): void
    {
        $vendor = User::factory()->vendeur()->create();
        $product = Product::factory()->create(['user_id' => $vendor->id, 'title' => 'Charizard VMAX PSA 10']);
        $item = WantedItem::create(['user_id' => User::factory()->create()->id, 'title' => 'Charizard VMAX', 'status' => 'ouverte']);

        $item->responses()->create([
            'seller_id' => $vendor->id,
            'content' => "J'ai cet exemplaire.",
            'product_id' => $product->id,
        ]);

        $this->get("/recherches/{$item->id}")->assertSee('Charizard VMAX PSA 10');
    }
}
