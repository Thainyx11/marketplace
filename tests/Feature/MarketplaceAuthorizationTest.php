<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke tests for the role/approval gates that are specific to this
 * marketplace (as opposed to Breeze's own stock auth tests).
 */
class MarketplaceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_reach_vendor_area(): void
    {
        $this->get('/vendeur/produits')->assertRedirect('/login');
    }

    public function test_buyer_cannot_reach_vendor_area(): void
    {
        $buyer = User::factory()->create(['role' => 'acheteur']);

        $this->actingAs($buyer)->get('/vendeur/produits')->assertForbidden();
    }

    public function test_pending_vendor_cannot_create_products(): void
    {
        $vendor = User::factory()->create(['role' => 'vendeur', 'is_approved' => false]);

        $this->assertFalse($vendor->can('create', Product::class));
    }

    public function test_approved_vendor_can_create_products(): void
    {
        $vendor = User::factory()->create(['role' => 'vendeur', 'is_approved' => true]);

        $this->assertTrue($vendor->can('create', Product::class));
    }

    public function test_non_admin_cannot_reach_admin_area(): void
    {
        $vendor = User::factory()->create(['role' => 'vendeur', 'is_approved' => true]);

        $this->actingAs($vendor)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_reach_admin_area(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}
