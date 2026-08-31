<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression guard: no sitemap or dynamic robots.txt existed at all before —
 * public/robots.txt was a static file that would have silently shadowed a
 * route of the same name (PHP's built-in server, and most web servers, serve
 * an existing file in the docroot before ever reaching the router), so it was
 * deleted as part of this fix rather than left to conflict.
 */
class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_an_active_product_but_not_a_hidden_one(): void
    {
        $active = Product::factory()->create(['status' => 'active']);
        $hidden = Product::factory()->create(['status' => 'hidden']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('products.show', $active->slug), false)
            ->assertDontSee(route('products.show', $hidden->slug), false);
    }

    public function test_sitemap_lists_an_approved_active_vendor_shop(): void
    {
        $this->seed(RoleSeeder::class);

        $vendor = User::factory()->create([
            'role' => 'vendeur',
            'is_approved' => true,
            'is_active' => true,
            'shop_slug' => 'boutique-test',
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('sellers.show', $vendor->shop_slug), false);
    }

    public function test_robots_txt_references_the_sitemap_with_an_absolute_url(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee(route('sitemap'), false);
    }
}
