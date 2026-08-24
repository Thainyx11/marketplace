<?php

namespace Tests\Feature;

use App\Livewire\Components\WishlistToggle;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_view_wishlist_page(): void
    {
        $this->get('/favoris')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_toggle_wishlist(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Livewire::actingAs($user)
            ->test(WishlistToggle::class, ['product' => $product])
            ->call('toggle')
            ->assertSet('isWishlisted', true);

        $this->assertDatabaseHas('wishlist_items', ['user_id' => $user->id, 'product_id' => $product->id]);

        Livewire::actingAs($user)
            ->test(WishlistToggle::class, ['product' => $product])
            ->call('toggle')
            ->assertSet('isWishlisted', false);

        $this->assertDatabaseMissing('wishlist_items', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_wishlist_index_only_shows_own_items(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $myProduct = Product::factory()->create(['title' => 'Mon favori']);
        $otherProduct = Product::factory()->create(['title' => 'Favori des autres']);

        $user->wishlistedProducts()->attach($myProduct);
        $other->wishlistedProducts()->attach($otherProduct);

        $response = $this->actingAs($user)->get('/favoris');

        $response->assertOk();
        $response->assertSee('Mon favori');
        $response->assertDontSee('Favori des autres');
    }
}
