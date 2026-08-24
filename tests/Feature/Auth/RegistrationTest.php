<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_registration_choice_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register-choice');
    }

    public function test_new_buyer_can_register_and_gets_instant_access(): void
    {
        $component = Volt::test('pages.auth.register-acheteur')
            ->set('name', 'Test Buyer')
            ->set('email', 'buyer@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isAcheteur());
        $this->assertTrue(auth()->user()->is_approved);
    }

    public function test_new_vendor_can_register_but_needs_approval(): void
    {
        $component = Volt::test('pages.auth.register-vendeur')
            ->set('name', 'Test Vendor')
            ->set('shop_name', 'Ma Boutique')
            ->set('email', 'vendor@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->isVendeur());
        $this->assertFalse(auth()->user()->is_approved);
    }
}
