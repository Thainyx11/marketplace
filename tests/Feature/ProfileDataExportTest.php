<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** RGPD Article 20 (portabilité) : /profil/export-donnees. */
class ProfileDataExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/profil/export-donnees')->assertRedirect('/login');
    }

    public function test_authenticated_user_downloads_their_own_data_as_json(): void
    {
        $buyer = User::factory()->create(['name' => 'Camille Dupont']);
        $otherBuyer = User::factory()->create();
        $product = Product::factory()->create(['title' => 'Charizard VMAX']);

        $myOrder = Order::create([
            'buyer_id' => $buyer->id,
            'total' => 45.50,
            'status' => 'en_attente',
            'shipping_method' => 'standard',
            'shipping_address' => '12 rue des Cartes, 1000 Bruxelles',
        ]);
        $myOrder->items()->create([
            'product_id' => $product->id,
            'seller_id' => $product->user_id,
            'quantity' => 1,
            'unit_price' => 45.50,
            'status' => 'en_attente',
        ]);

        $otherOrder = Order::create([
            'buyer_id' => $otherBuyer->id,
            'total' => 12.00,
            'status' => 'en_attente',
            'shipping_method' => 'standard',
            'shipping_address' => '5 avenue des Figurines, 4000 Liège',
        ]);

        $response = $this->actingAs($buyer)->get('/profil/export-donnees');

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename="mes-donnees-marketplace-pop-culture.json"');

        $data = json_decode($response->getContent(), true);

        $this->assertSame('Camille Dupont', $data['profil']['nom']);
        $this->assertCount(1, $data['commandes']);
        $this->assertSame($myOrder->id, $data['commandes'][0]['id']);
        $this->assertSame('Charizard VMAX', $data['commandes'][0]['articles'][0]['produit']);

        // Privacy check: another buyer's order must not leak into this export.
        $orderIds = array_column($data['commandes'], 'id');
        $this->assertNotContains($otherOrder->id, $orderIds);
    }
}
