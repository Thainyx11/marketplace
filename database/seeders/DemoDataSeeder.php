<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@marketplace.test'],
            ['name' => 'Admin Marketplace', 'password' => bcrypt('password'), 'role' => 'admin'],
        );

        $vendeur = User::firstOrCreate(
            ['email' => 'vendeur@marketplace.test'],
            [
                'name' => 'Cartes & Figurines Léo', 'password' => bcrypt('password'), 'role' => 'vendeur',
                'is_approved' => true, 'shop_name' => 'Cartes & Figurines Léo', 'shop_slug' => 'cartes-figurines-leo',
                'bio' => 'Passionné de cartes Pokémon et de figurines depuis 15 ans.',
            ],
        );

        $vendeurPending = User::firstOrCreate(
            ['email' => 'vendeur.pending@marketplace.test'],
            [
                'name' => 'Retro Games Shop', 'password' => bcrypt('password'), 'role' => 'vendeur',
                'is_approved' => false, 'shop_name' => 'Retro Games Shop', 'shop_slug' => 'retro-games-shop',
            ],
        );

        $acheteur = User::firstOrCreate(
            ['email' => 'acheteur@marketplace.test'],
            [
                'name' => 'Camille Dupont', 'password' => bcrypt('password'), 'role' => 'acheteur',
                'shipping_address' => "12 rue des Collectionneurs\n75000 Paris, France",
            ],
        );

        $categories = Category::all()->keyBy('slug');

        $products = [
            ['title' => 'Pikachu Illustrator (reproduction)', 'category' => 'cartes-a-collectionner', 'price' => 120, 'condition' => 'comme_neuf', 'brand' => 'Pokemon', 'rarity' => 'secrete', 'stock' => 3],
            ['title' => 'Charizard VMAX', 'category' => 'cartes-a-collectionner', 'price' => 45.5, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 5],
            ['title' => 'Booster Magic The Gathering - Dominaria', 'category' => 'cartes-a-collectionner', 'price' => 8.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => 'commune', 'stock' => 20],
            ['title' => 'Super Mario 64 - Nintendo 64', 'category' => 'jeux-video', 'price' => 39.9, 'condition' => 'bon_etat', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 2],
            ['title' => 'Final Fantasy VII - PS1 (complet)', 'category' => 'jeux-video', 'price' => 59, 'condition' => 'bon_etat', 'brand' => 'PlayStation', 'rarity' => null, 'stock' => 1],
            ['title' => 'Manette GameCube violette', 'category' => 'jeux-video', 'price' => 25, 'condition' => 'usage', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 4],
            ['title' => 'Figurine Funko Pop Naruto', 'category' => 'figurines', 'price' => 15.9, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 10],
            ['title' => 'Figurine Ichiban Kuji Luffy', 'category' => 'figurines', 'price' => 34, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 6],
            ['title' => 'One Piece Tome 1 - édition originale', 'category' => 'manga', 'price' => 12, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 3],
            ['title' => 'Coffret Dragon Ball Z complet', 'category' => 'manga', 'price' => 89, 'condition' => 'comme_neuf', 'brand' => null, 'rarity' => null, 'stock' => 1],
            ['title' => 'Porte-clés Pokéball lumineux', 'category' => 'goodies', 'price' => 6.5, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 15],
            ['title' => 'Mug Zelda Triforce', 'category' => 'goodies', 'price' => 11.9, 'condition' => 'neuf', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 8],
        ];

        foreach ($products as $data) {
            Product::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($data['title'])],
                [
                    'user_id' => $vendeur->id,
                    'category_id' => $categories[$data['category']]->id,
                    'title' => $data['title'],
                    'description' => "Article d'occasion / collection vendu en l'état. {$data['title']}, en parfait état de fonctionnement.",
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'condition' => $data['condition'],
                    'brand' => $data['brand'],
                    'rarity' => $data['rarity'],
                    'status' => 'active',
                ],
            );
        }

        // A completed order with a review, so the delivered/review flow has data to show immediately.
        $charizard = Product::where('slug', 'charizard-vmax')->first();

        if ($charizard && ! Order::where('buyer_id', $acheteur->id)->exists()) {
            $order = Order::create([
                'buyer_id' => $acheteur->id,
                'total' => $charizard->price,
                'status' => 'livree',
                'shipping_address' => $acheteur->shipping_address,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $charizard->id,
                'seller_id' => $charizard->user_id,
                'quantity' => 1,
                'unit_price' => $charizard->price,
                'status' => 'livree',
            ]);

            Payment::create([
                'order_id' => $order->id,
                'stripe_id' => 'demo_seed_'.$order->id,
                'amount' => $charizard->price,
                'commission' => round($charizard->price * 0.05, 2),
                'status' => 'paid',
            ]);

            Review::create([
                'order_id' => $order->id,
                'product_id' => $charizard->id,
                'rating' => 5,
                'comment' => 'Carte reçue rapidement et conforme à la description, vendeur très sérieux !',
            ]);

            Message::create([
                'sender_id' => $acheteur->id,
                'receiver_id' => $vendeur->id,
                'product_id' => $charizard->id,
                'content' => 'Bonjour, la carte est-elle toujours disponible ?',
                'seen' => true,
            ]);

            Message::create([
                'sender_id' => $vendeur->id,
                'receiver_id' => $acheteur->id,
                'product_id' => $charizard->id,
                'content' => 'Bonjour, oui elle est disponible, je vous l\'envoie dès réception du paiement.',
                'seen' => false,
            ]);
        }

        $this->command?->info('Comptes de test : admin@marketplace.test / vendeur@marketplace.test / vendeur.pending@marketplace.test / acheteur@marketplace.test — mot de passe : password');
    }
}
