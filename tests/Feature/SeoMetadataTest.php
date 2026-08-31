<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression guard for the SEO fix: every page used to share the same static
 * site-wide <title> and no meta description at all — the product page (the
 * single most SEO-valuable page on a marketplace, one per listing) had no way
 * to rank on its own product name. Volt's class-based components have no
 * built-in title() convention (that only exists for Volt's functional API,
 * confirmed by reading vendor/livewire/volt's Component::render()), so the
 * fix chains the same View::title()/layoutData() macros Volt itself uses
 * internally, via a render() override on the product show page.
 */
class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_a_default_title_and_meta_description(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Marketplace Pop Culture</title>', false)
            ->assertSee('<meta name="description"', false);
    }

    public function test_product_page_title_and_meta_description_are_specific_to_the_product(): void
    {
        $product = Product::factory()->create([
            'title' => 'Vaporeon Holo — Jungle',
            'description' => 'Une carte Pokémon rare en excellent état, jamais jouée.',
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertOk()
            ->assertSee('<title>Vaporeon Holo — Jungle — Marketplace Pop Culture</title>', false)
            ->assertSee('Une carte Pokémon rare en excellent état', false);
    }

    public function test_catalog_page_title_reflects_the_selected_category(): void
    {
        $category = Category::factory()->create(['name' => 'Cartes à collectionner', 'slug' => 'cartes-a-collectionner']);

        $this->get(route('products.index', ['categorie' => $category->slug]))
            ->assertOk()
            ->assertSee('<title>Cartes à collectionner — Catalogue — Marketplace Pop Culture</title>', false);
    }

    public function test_seller_shop_page_title_uses_the_shop_name(): void
    {
        $this->seed(RoleSeeder::class);

        $vendor = User::factory()->create([
            'role' => 'vendeur',
            'shop_name' => 'PopGoodies Boutique',
            'shop_slug' => 'popgoodies-boutique',
        ]);

        $this->get(route('sellers.show', $vendor->shop_slug))
            ->assertOk()
            ->assertSee('<title>PopGoodies Boutique — Marketplace Pop Culture</title>', false);
    }
}
