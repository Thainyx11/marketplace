<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Real (if generic) stock photos per category via LoremFlickr — keywords
     * verified reachable from this environment. `?lock=N` pins a stable pick
     * per product so re-seeding doesn't shuffle every photo.
     */
    private const CATEGORY_KEYWORDS = [
        'cartes-a-collectionner' => ['pokemon', 'collectible+card'],
        'jeux-video' => ['retrogaming', 'nintendo', 'videogame'],
        'figurines' => ['figurine', 'toy', 'anime'],
        'manga' => ['manga', 'comic'],
        'goodies' => ['goodies', 'souvenir'],
    ];

    public function run(): void
    {
        if (! Setting::where('key', 'legal_notice')->exists()) {
            Setting::set('legal_notice', "Mentions légales\n\nMarketplace Pop Culture est une plateforme de mise en relation entre vendeurs et acheteurs particuliers d'objets de collection liés à la pop culture (cartes, jeux vidéo, figurines, manga, goodies).\n\nÉditeur : Marketplace Pop Culture (projet de Travail de Fin d'Études).\nHébergement : environnement de développement local.\n\nConditions générales d'utilisation\n\n1. Chaque vendeur est responsable de l'exactitude des annonces qu'il publie et de la conformité des objets vendus.\n2. Une commission est prélevée par la plateforme sur chaque transaction, dont le taux est indiqué avant validation de la commande.\n3. Les données personnelles collectées (nom, email, adresse de livraison) sont utilisées exclusivement pour le bon fonctionnement des commandes et ne sont pas cédées à des tiers.\n4. Conformément au RGPD, chaque utilisateur peut demander la suppression de son compte et de ses données depuis la page Profil.\n5. Tout litige entre un acheteur et un vendeur peut être signalé à l'administration, qui pourra intervenir sur la commande concernée.");
        }

        User::firstOrCreate(
            ['email' => 'admin@marketplace.test'],
            ['name' => 'Admin Marketplace', 'password' => bcrypt('password'), 'role' => 'admin'],
        );

        $acheteur = User::firstOrCreate(
            ['email' => 'acheteur@marketplace.test'],
            [
                'name' => 'Camille Dupont', 'password' => bcrypt('password'), 'role' => 'acheteur',
                'shipping_address' => "12 rue des Collectionneurs\n75000 Paris, France",
            ],
        );

        User::firstOrCreate(
            ['email' => 'vendeur.pending@marketplace.test'],
            [
                'name' => 'Retro Games Shop', 'password' => bcrypt('password'), 'role' => 'vendeur',
                'is_approved' => false, 'shop_name' => 'Retro Games Shop', 'shop_slug' => 'retro-games-shop',
            ],
        );

        $vendorDefs = [
            'leo' => ['email' => 'vendeur@marketplace.test', 'name' => 'Léo Martin', 'shop_name' => 'Cartes & Figurines Léo', 'shop_slug' => 'cartes-figurines-leo', 'bio' => 'Passionné de cartes Pokémon et de figurines depuis 15 ans.'],
            'tck' => ['name' => 'Antoine Roche', 'shop_name' => 'Trading Card Kingdom', 'shop_slug' => 'trading-card-kingdom', 'bio' => 'Spécialiste Magic, Pokémon et Yu-Gi-Oh — cartes gradées et éditions rares.'],
            'retropixel' => ['name' => 'Julien Faure', 'shop_name' => 'RetroPixel Games', 'shop_slug' => 'retropixel-games', 'bio' => 'Consoles et jeux rétro testés et fonctionnels, du NES à la Dreamcast.'],
            'mangacorner' => ['name' => 'Sofia Marchetti', 'shop_name' => 'Manga Corner', 'shop_slug' => 'manga-corner', 'bio' => "Mangas d'occasion en bon état, éditions originales et coffrets complets."],
            'popgoodies' => ['name' => 'Nora Bensaid', 'shop_name' => 'PopGoodies Boutique', 'shop_slug' => 'popgoodies-boutique', 'bio' => 'Goodies, peluches et objets dérivés pour tous les fandoms.'],
            'funko' => ['name' => 'Maxime Girard', 'shop_name' => 'Funko & Friends', 'shop_slug' => 'funko-and-friends', 'bio' => 'Collection de Funko Pop et figurines articulées, neuves sous boîte.'],
        ];

        $vendors = [];

        foreach ($vendorDefs as $key => $def) {
            $vendors[$key] = User::firstOrCreate(
                ['email' => $def['email'] ?? "$key@marketplace.test"],
                [
                    'name' => $def['name'], 'password' => bcrypt('password'), 'role' => 'vendeur',
                    'is_approved' => true, 'shop_name' => $def['shop_name'], 'shop_slug' => $def['shop_slug'],
                    'bio' => $def['bio'],
                ],
            );
        }

        $categories = Category::all()->keyBy('slug');

        $products = [
            // --- Cartes à collectionner : Léo ---
            ['vendor' => 'leo', 'title' => 'Pikachu Illustrator (reproduction)', 'category' => 'cartes-a-collectionner', 'price' => 120, 'condition' => 'comme_neuf', 'brand' => 'Pokemon', 'rarity' => 'secrete', 'stock' => 3],
            ['vendor' => 'leo', 'title' => 'Charizard VMAX', 'category' => 'cartes-a-collectionner', 'price' => 45.5, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 5],
            ['vendor' => 'leo', 'title' => 'Booster Magic The Gathering - Dominaria', 'category' => 'cartes-a-collectionner', 'price' => 8.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => 'commune', 'stock' => 20],
            ['vendor' => 'leo', 'title' => 'Blastoise EX', 'category' => 'cartes-a-collectionner', 'price' => 28, 'condition' => 'bon_etat', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 4],
            ['vendor' => 'leo', 'title' => 'Dracaufeu Reverse Édition Française', 'category' => 'cartes-a-collectionner', 'price' => 62, 'condition' => 'comme_neuf', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 2],
            ['vendor' => 'leo', 'title' => 'Mewtwo GX', 'category' => 'cartes-a-collectionner', 'price' => 19.5, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => 'rare', 'stock' => 6],

            // --- Cartes à collectionner : Trading Card Kingdom ---
            ['vendor' => 'tck', 'title' => 'Black Lotus Alpha (reproduction certifiée)', 'category' => 'cartes-a-collectionner', 'price' => 89, 'condition' => 'neuf', 'brand' => null, 'rarity' => 'secrete', 'stock' => 2],
            ['vendor' => 'tck', 'title' => 'Booster Yu-Gi-Oh! Legend of Blue Eyes', 'category' => 'cartes-a-collectionner', 'price' => 14.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => 'rare', 'stock' => 12],
            ['vendor' => 'tck', 'title' => 'Jace the Mind Sculptor - Magic', 'category' => 'cartes-a-collectionner', 'price' => 55, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => 'holo', 'stock' => 3],
            ['vendor' => 'tck', 'title' => 'Display 36 boosters Écarlate et Violet', 'category' => 'cartes-a-collectionner', 'price' => 145, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 5],
            ['vendor' => 'tck', 'title' => 'Dracaufeu VSTAR Rainbow Rare', 'category' => 'cartes-a-collectionner', 'price' => 78, 'condition' => 'comme_neuf', 'brand' => 'Pokemon', 'rarity' => 'secrete', 'stock' => 2],
            ['vendor' => 'tck', 'title' => 'Mew EX Full Art', 'category' => 'cartes-a-collectionner', 'price' => 22, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 7],
            ['vendor' => 'tck', 'title' => 'Salamence VMAX', 'category' => 'cartes-a-collectionner', 'price' => 17.5, 'condition' => 'bon_etat', 'brand' => 'Pokemon', 'rarity' => 'rare', 'stock' => 8],
            ['vendor' => 'tck', 'title' => 'Booster One Piece Card Game - Romance Dawn', 'category' => 'cartes-a-collectionner', 'price' => 5.5, 'condition' => 'neuf', 'brand' => null, 'rarity' => 'commune', 'stock' => 30],
            ['vendor' => 'tck', 'title' => 'Deck complet Yu-Gi-Oh Kaiba', 'category' => 'cartes-a-collectionner', 'price' => 34, 'condition' => 'comme_neuf', 'brand' => null, 'rarity' => 'rare', 'stock' => 4],
            ['vendor' => 'tck', 'title' => 'Umbreon VMAX Alt Art', 'category' => 'cartes-a-collectionner', 'price' => 210, 'condition' => 'comme_neuf', 'brand' => 'Pokemon', 'rarity' => 'secrete', 'stock' => 1],
            ['vendor' => 'tck', 'title' => 'Lugia Neo Genesis 1er Édition', 'category' => 'cartes-a-collectionner', 'price' => 165, 'condition' => 'bon_etat', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 1],

            // --- Jeux vidéo : RetroPixel Games ---
            ['vendor' => 'retropixel', 'title' => 'Super Mario 64 - Nintendo 64', 'category' => 'jeux-video', 'price' => 39.9, 'condition' => 'bon_etat', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 2],
            ['vendor' => 'retropixel', 'title' => 'Final Fantasy VII - PS1 (complet)', 'category' => 'jeux-video', 'price' => 59, 'condition' => 'bon_etat', 'brand' => 'PlayStation', 'rarity' => null, 'stock' => 1],
            ['vendor' => 'retropixel', 'title' => 'Manette GameCube violette', 'category' => 'jeux-video', 'price' => 25, 'condition' => 'usage', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 4],
            ['vendor' => 'retropixel', 'title' => 'Zelda Ocarina of Time - Nintendo 64', 'category' => 'jeux-video', 'price' => 44.9, 'condition' => 'bon_etat', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 2],
            ['vendor' => 'retropixel', 'title' => 'Sonic Adventure 2 - Dreamcast', 'category' => 'jeux-video', 'price' => 32, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 1],
            ['vendor' => 'retropixel', 'title' => 'Metal Gear Solid - PS1', 'category' => 'jeux-video', 'price' => 27.5, 'condition' => 'usage', 'brand' => 'PlayStation', 'rarity' => null, 'stock' => 3],
            ['vendor' => 'retropixel', 'title' => 'Pokémon Version Or HeartGold - DS', 'category' => 'jeux-video', 'price' => 68, 'condition' => 'bon_etat', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 1],
            ['vendor' => 'retropixel', 'title' => 'Console Game Boy Color transparente', 'category' => 'jeux-video', 'price' => 55, 'condition' => 'bon_etat', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 2],
            ['vendor' => 'retropixel', 'title' => 'Manette Xbox 360 sans fil', 'category' => 'jeux-video', 'price' => 18, 'condition' => 'usage', 'brand' => null, 'rarity' => null, 'stock' => 5],
            ['vendor' => 'retropixel', 'title' => 'Crash Bandicoot 3 - PS1', 'category' => 'jeux-video', 'price' => 24.9, 'condition' => 'bon_etat', 'brand' => 'PlayStation', 'rarity' => null, 'stock' => 2],
            ['vendor' => 'retropixel', 'title' => 'Console Nintendo 64 grise + câbles', 'category' => 'jeux-video', 'price' => 79, 'condition' => 'bon_etat', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 1],

            // --- Figurines : Léo + Funko & Friends ---
            ['vendor' => 'leo', 'title' => 'Figurine Funko Pop Naruto', 'category' => 'figurines', 'price' => 15.9, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 10],
            ['vendor' => 'leo', 'title' => 'Figurine Ichiban Kuji Luffy', 'category' => 'figurines', 'price' => 34, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 6],
            ['vendor' => 'funko', 'title' => 'Funko Pop Batman', 'category' => 'figurines', 'price' => 13.9, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 12],
            ['vendor' => 'funko', 'title' => 'Funko Pop Harry Potter - Dumbledore', 'category' => 'figurines', 'price' => 14.5, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 8],
            ['vendor' => 'funko', 'title' => 'Figurine Dragon Ball - Goku Ultra Instinct', 'category' => 'figurines', 'price' => 42, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 4],
            ['vendor' => 'funko', 'title' => 'Statuette My Hero Academia - Deku', 'category' => 'figurines', 'price' => 38, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 3],
            ['vendor' => 'funko', 'title' => 'Funko Pop Star Wars - Baby Yoda', 'category' => 'figurines', 'price' => 16.9, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 15],
            ['vendor' => 'funko', 'title' => 'Nendoroid Sailor Moon', 'category' => 'figurines', 'price' => 49, 'condition' => 'comme_neuf', 'brand' => null, 'rarity' => null, 'stock' => 2],
            ['vendor' => 'funko', 'title' => 'Funko Pop Marvel - Spider-Man', 'category' => 'figurines', 'price' => 13.9, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 11],
            ['vendor' => 'funko', 'title' => 'Figurine Demon Slayer - Tanjiro', 'category' => 'figurines', 'price' => 29.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 5],

            // --- Manga : Manga Corner ---
            ['vendor' => 'mangacorner', 'title' => 'One Piece Tome 1 - édition originale', 'category' => 'manga', 'price' => 12, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 3],
            ['vendor' => 'mangacorner', 'title' => 'Coffret Dragon Ball Z complet', 'category' => 'manga', 'price' => 89, 'condition' => 'comme_neuf', 'brand' => null, 'rarity' => null, 'stock' => 1],
            ['vendor' => 'mangacorner', 'title' => 'Naruto Tome 1 - édition originale', 'category' => 'manga', 'price' => 9.5, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 5],
            ['vendor' => 'mangacorner', 'title' => "L'Attaque des Titans - Coffret complet", 'category' => 'manga', 'price' => 145, 'condition' => 'comme_neuf', 'brand' => null, 'rarity' => null, 'stock' => 1],
            ['vendor' => 'mangacorner', 'title' => 'Death Note Tomes 1 à 5', 'category' => 'manga', 'price' => 38, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 2],
            ['vendor' => 'mangacorner', 'title' => 'Demon Slayer Tome 1', 'category' => 'manga', 'price' => 7.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 8],
            ['vendor' => 'mangacorner', 'title' => 'One Punch Man Tome 1', 'category' => 'manga', 'price' => 8.5, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 6],
            ['vendor' => 'mangacorner', 'title' => 'Fullmetal Alchemist - Coffret intégral', 'category' => 'manga', 'price' => 120, 'condition' => 'comme_neuf', 'brand' => null, 'rarity' => null, 'stock' => 1],
            ['vendor' => 'mangacorner', 'title' => 'My Hero Academia Tomes 1 à 3', 'category' => 'manga', 'price' => 22, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 3],

            // --- Goodies : PopGoodies ---
            ['vendor' => 'popgoodies', 'title' => 'Porte-clés Pokéball lumineux', 'category' => 'goodies', 'price' => 6.5, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 15],
            ['vendor' => 'popgoodies', 'title' => 'Mug Zelda Triforce', 'category' => 'goodies', 'price' => 11.9, 'condition' => 'neuf', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 8],
            ['vendor' => 'popgoodies', 'title' => 'Peluche Pikachu 30cm', 'category' => 'goodies', 'price' => 19.9, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 10],
            ['vendor' => 'popgoodies', 'title' => "Poster L'Attaque des Titans", 'category' => 'goodies', 'price' => 8.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 20],
            ['vendor' => 'popgoodies', 'title' => "Set de pin's Pokémon", 'category' => 'goodies', 'price' => 9.5, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 12],
            ['vendor' => 'popgoodies', 'title' => 'Coussin Totoro', 'category' => 'goodies', 'price' => 17.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 6],
            ['vendor' => 'popgoodies', 'title' => 'Gourde Harry Potter Poudlard', 'category' => 'goodies', 'price' => 13.5, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 9],
            ['vendor' => 'popgoodies', 'title' => 'Casquette Super Mario', 'category' => 'goodies', 'price' => 15, 'condition' => 'neuf', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 7],
            ['vendor' => 'popgoodies', 'title' => 'Sac à dos Naruto Akatsuki', 'category' => 'goodies', 'price' => 32, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 4],
            ['vendor' => 'popgoodies', 'title' => 'Tapis de souris One Piece XXL', 'category' => 'goodies', 'price' => 14.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 10],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'user_id' => $vendors[$data['vendor']]->id,
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

            if ($product->wasRecentlyCreated) {
                $keywords = self::CATEGORY_KEYWORDS[$data['category']];
                $keyword = $keywords[$product->id % count($keywords)];

                ProductImage::create(['product_id' => $product->id, 'path' => "https://loremflickr.com/800/800/{$keyword}?lock={$product->id}", 'position' => 0]);
                ProductImage::create(['product_id' => $product->id, 'path' => "https://loremflickr.com/800/800/{$keyword}?lock=".($product->id + 1000), 'position' => 1]);
            }
        }

        // A completed order with a review, so the delivered/review flow has data to show immediately.
        $charizard = Product::where('slug', 'charizard-vmax')->first();
        $vendeur = $vendors['leo'];

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

        $vendorEmails = array_map(fn ($key, $def) => $def['email'] ?? "$key@marketplace.test", array_keys($vendorDefs), $vendorDefs);

        $this->command?->info('Comptes de test (mot de passe: password) : admin@marketplace.test, acheteur@marketplace.test, vendeur.pending@marketplace.test, et '.implode(', ', $vendorEmails));
    }
}
