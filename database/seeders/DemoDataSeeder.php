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

    /**
     * Real, exact product photos resolved once from open/keyless data APIs
     * and hardcoded here — matches this seeder's existing pattern of storing
     * static external URLs rather than fetching live at seed time:
     *   - Cards: TCGdex (Pokémon) and Scryfall (Magic)
     *   - Jeux vidéo: libretro-thumbnails (curated box-art, GitHub-hosted)
     *   - Manga: MangaDex (volume-1 / tome-1 covers)
     * Sealed/bundle products (boosters, coffrets, multi-tome packs, consoles,
     * controllers) and the few titles these sources don't carry (Pikachu
     * Illustrator, Zelda BOTW - no Switch box-art repo) are intentionally
     * left out and keep the generic LoremFlickr photo below.
     */
    private const IMAGE_OVERRIDES = [
        // Cartes à collectionner
        'Charizard VMAX' => 'https://assets.tcgdex.net/en/swsh/swsh3/20/high.png',
        'Blastoise EX' => 'https://assets.tcgdex.net/en/xy/g1/17/high.png',
        'Dracaufeu Reverse Édition Française' => 'https://assets.tcgdex.net/fr/base/base1/4/high.png',
        'Mewtwo GX' => 'https://assets.tcgdex.net/en/sm/sm115/31/high.png',
        'Dracaufeu VSTAR Rainbow Rare' => 'https://assets.tcgdex.net/fr/swsh/swsh9/018/high.png',
        'Mew EX Full Art' => 'https://assets.tcgdex.net/en/tcgp/A1a/032/high.png',
        'Salamence VMAX' => 'https://assets.tcgdex.net/en/swsh/swsh3/144/high.png',
        'Umbreon VMAX Alt Art' => 'https://assets.tcgdex.net/en/swsh/swsh7/215/high.png',
        'Lugia Neo Genesis 1er Édition' => 'https://assets.tcgdex.net/en/neo/neo1/9/high.png',
        'Snorlax Holo - Jungle' => 'https://assets.tcgdex.net/en/base/base2/11/high.png',
        'Rayquaza VMAX Alt Art' => 'https://assets.tcgdex.net/en/swsh/swsh7/218/high.png',
        'Dracaufeu ex - Écarlate et Violet' => 'https://assets.tcgdex.net/fr/sv/sv03.5/006/high.png',
        'Venusaur Holo - Base Set' => 'https://assets.tcgdex.net/en/base/base1/15/high.png',
        'Gyarados 1ère Édition Shadowless' => 'https://assets.tcgdex.net/en/base/base1/6/high.png',
        'Charizard Base Set Shadowless' => 'https://assets.tcgdex.net/en/base/base1/4/high.png',
        'Eevee GX - Soleil et Lune' => 'https://assets.tcgdex.net/en/sm/smp/SM174/high.png',
        'Espeon VMAX Alt Art' => 'https://assets.tcgdex.net/en/swsh/swsh8/270/high.png',
        'Black Lotus Alpha (reproduction certifiée)' => 'https://cards.scryfall.io/normal/front/b/0/b0faa7f2-b547-42c4-a810-839da50dadfe.jpg',
        'Jace the Mind Sculptor - Magic' => 'https://cards.scryfall.io/normal/front/c/8/c8817585-0d32-4d56-9142-0d29512e86a9.jpg',
        'Sol Ring - Magic Commander' => 'https://cards.scryfall.io/normal/front/9/1/91fdb56b-54d5-4272-8319-505ff987fe9b.jpg',
        'Force of Will - Carte Magic rare' => 'https://cards.scryfall.io/normal/front/8/9/89f612d6-7c59-4a7b-a87d-45f789e88ba5.jpg',

        // Jeux vidéo
        'Super Mario 64 - Nintendo 64' => 'https://raw.githubusercontent.com/libretro-thumbnails/Nintendo_-_Nintendo_64/master/Named_Boxarts/Super%20Mario%2064%20%28Europe%29%20%28En%2CFr%2CDe%29.png',
        'Final Fantasy VII - PS1 (complet)' => 'https://raw.githubusercontent.com/libretro-thumbnails/Sony_-_PlayStation/master/Named_Boxarts/Final%20Fantasy%20VII%20%28France%29%20%28Disc%201%29.png',
        'Zelda Ocarina of Time - Nintendo 64' => 'https://raw.githubusercontent.com/libretro-thumbnails/Nintendo_-_Nintendo_64/master/Named_Boxarts/Legend%20of%20Zelda%2C%20The%20-%20Ocarina%20of%20Time%20%28Europe%29%20%28En%2CFr%2CDe%29.png',
        'Sonic Adventure 2 - Dreamcast' => 'https://raw.githubusercontent.com/libretro-thumbnails/Sega_-_Dreamcast/master/Named_Boxarts/Sonic%20Adventure%202%20%28Europe%29%20%28En%2CJa%2CFr%2CDe%2CEs%29.png',
        'Metal Gear Solid - PS1' => 'https://raw.githubusercontent.com/libretro-thumbnails/Sony_-_PlayStation/master/Named_Boxarts/Metal%20Gear%20Solid%20%28France%29%20%28Disc%201%29.png',
        'Pokémon Version Or HeartGold - DS' => 'https://raw.githubusercontent.com/libretro-thumbnails/Nintendo_-_Nintendo_DS/master/Named_Boxarts/Pokemon%20-%20Version%20Or%20HeartGold%20%28France%29.png',
        'Crash Bandicoot 3 - PS1' => 'https://raw.githubusercontent.com/libretro-thumbnails/Sony_-_PlayStation/master/Named_Boxarts/Crash%20Bandicoot%203%20-%20Warped%20%28Europe%29%20%28En%2CFr%2CDe%2CEs%2CIt%29.png',
        'Super Smash Bros Melee - GameCube' => 'https://raw.githubusercontent.com/libretro-thumbnails/Nintendo_-_GameCube/master/Named_Boxarts/Super%20Smash%20Bros.%20Melee%20%28Europe%29%20%28En%2CFr%2CDe%2CEs%2CIt%29.png',
        'Chrono Trigger - SNES' => 'https://raw.githubusercontent.com/libretro-thumbnails/Nintendo_-_Super_Nintendo_Entertainment_System/master/Named_Boxarts/Chrono%20Trigger%20%28USA%29.png',

        // Manga (couverture du tome 1 / premier tome du coffret)
        'One Piece Tome 1 - édition originale' => 'https://uploads.mangadex.org/covers/a1c7c817-4e59-43b7-9365-09675a149a6f/2f4aca53-64c7-46ac-ae85-3bc9b3169890.png',
        'Naruto Tome 1 - édition originale' => 'https://uploads.mangadex.org/covers/6b1eb93e-473a-4ab3-9922-1a66d2a29a4a/c5a3090c-4ca0-40a2-9102-e0ee0c6dac15.jpg',
        'Demon Slayer Tome 1' => 'https://uploads.mangadex.org/covers/789642f8-ca89-4e4e-8f7b-eee4d17ea08b/28b64721-11b1-4936-a1c4-1b5bef7815ab.jpg',
        'One Punch Man Tome 1' => 'https://uploads.mangadex.org/covers/d8a959f7-648e-4c8d-8f23-f1f3f8e129f3/dfc14954-f855-47a3-9401-4abe2a78621a.jpg',
        'Chainsaw Man Tome 1' => 'https://uploads.mangadex.org/covers/a77742b1-befd-49a4-bff5-1ad4e6b0ef7b/07b6e139-194a-4438-b07a-57db2f4f22f8.jpg',
        'Jujutsu Kaisen Tome 1' => 'https://uploads.mangadex.org/covers/c52b2ce3-7f95-469c-96b0-479524fb7a1a/258999da-cbcf-4dd9-8786-91f5eaa968b8.png',
        'Spy x Family Tome 1' => 'https://uploads.mangadex.org/covers/6b958848-c885-4735-9201-12ee77abcb3c/930499de-1241-41d5-a329-6f35f861720b.jpg',
        'Berserk - Coffret Deluxe Vol.1' => 'https://uploads.mangadex.org/covers/801513ba-a712-498c-8f57-cae55b38cc92/88b10820-0309-44c5-9a40-c799865ad968.jpg',
        'Vinland Saga Tome 1' => 'https://uploads.mangadex.org/covers/5d1fc77e-706a-4fc5-bea8-486c9be0145d/47e19a12-b0fb-4b52-b105-c202c555b966.jpg',
        'Vagabond Tome 1' => 'https://uploads.mangadex.org/covers/d1a9fdeb-f713-407f-960c-8326b586e6fd/00cf99f7-5145-44ea-a068-0a8a5cd4dc76.jpg',
        'Tokyo Ghoul Tome 1' => 'https://uploads.mangadex.org/covers/6a1d1cb1-ecd5-40d9-89ff-9d88e40b136b/040e8ae9-4ddd-49d2-8986-56782b391714.jpg',
        'Bleach Tome 1' => 'https://uploads.mangadex.org/covers/239d6260-d71f-43b0-afff-074e3619e3de/3cbb1b1c-6630-4971-b2b1-e24e6cbf4f40.jpg',
    ];

    public function run(): void
    {
        if (! Setting::where('key', 'legal_notice')->exists()) {
            Setting::set('legal_notice', "Mentions légales\n\nMarketplace Pop Culture est une plateforme de mise en relation entre vendeurs et acheteurs particuliers d'objets de collection liés à la pop culture (cartes, jeux vidéo, figurines, manga, goodies).\n\nÉditeur : Marketplace Pop Culture (projet de Travail de Fin d'Études).\nHébergement : environnement de développement local.\n\nConditions générales d'utilisation\n\n1. Chaque vendeur est responsable de l'exactitude des annonces qu'il publie et de la conformité des objets vendus.\n2. Une commission est prélevée par la plateforme sur chaque transaction, dont le taux est indiqué avant validation de la commande.\n3. Les données personnelles collectées (nom, email, adresse de livraison) sont utilisées exclusivement pour le bon fonctionnement des commandes et ne sont pas cédées à des tiers.\n4. Conformément au RGPD, chaque utilisateur peut demander la suppression de son compte et de ses données depuis la page Profil.\n5. Tout litige entre un acheteur et un vendeur peut être signalé à l'administration, qui pourra intervenir sur la commande concernée.");
        }

        // The admin can also own a baseline product catalog (ProductPolicy::create),
        // so the marketplace is never empty regardless of real vendor signups —
        // it needs a shop identity like any other seller to do that.
        $admin = User::firstOrCreate(
            ['email' => 'admin@marketplace.test'],
            [
                'name' => 'Admin Marketplace', 'password' => bcrypt('password'), 'role' => 'admin',
                'shop_name' => 'Sélection Officielle', 'shop_slug' => 'selection-officielle',
                'bio' => "La sélection officielle de la marketplace : une base d'objets pop culture toujours disponible, tous univers confondus.",
            ],
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

        $vendors = ['admin' => $admin];

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

            // --- Sélection Officielle (admin) : baseline permanente sur toutes les catégories ---
            ['vendor' => 'admin', 'title' => 'Snorlax Holo - Jungle', 'category' => 'cartes-a-collectionner', 'price' => 24.9, 'condition' => 'bon_etat', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 6],
            ['vendor' => 'admin', 'title' => 'Rayquaza VMAX Alt Art', 'category' => 'cartes-a-collectionner', 'price' => 95, 'condition' => 'comme_neuf', 'brand' => 'Pokemon', 'rarity' => 'secrete', 'stock' => 2],
            ['vendor' => 'admin', 'title' => 'Booster Pokémon 151', 'category' => 'cartes-a-collectionner', 'price' => 6.9, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => 'commune', 'stock' => 40],
            ['vendor' => 'admin', 'title' => 'Sol Ring - Magic Commander', 'category' => 'cartes-a-collectionner', 'price' => 4.5, 'condition' => 'neuf', 'brand' => null, 'rarity' => 'commune', 'stock' => 25],
            ['vendor' => 'admin', 'title' => 'Dracaufeu ex - Écarlate et Violet', 'category' => 'cartes-a-collectionner', 'price' => 12.9, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 10],
            ['vendor' => 'admin', 'title' => 'Console PS2 Slim + 2 manettes', 'category' => 'jeux-video', 'price' => 89, 'condition' => 'bon_etat', 'brand' => 'PlayStation', 'rarity' => null, 'stock' => 2],
            ['vendor' => 'admin', 'title' => 'The Legend of Zelda: Breath of the Wild - Switch', 'category' => 'jeux-video', 'price' => 42, 'condition' => 'comme_neuf', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 5],
            ['vendor' => 'admin', 'title' => 'Super Smash Bros Melee - GameCube', 'category' => 'jeux-video', 'price' => 34.9, 'condition' => 'bon_etat', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 3],
            ['vendor' => 'admin', 'title' => 'Console Sega Mega Drive II', 'category' => 'jeux-video', 'price' => 65, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 1],
            ['vendor' => 'admin', 'title' => 'Manette Nintendo Switch Pro', 'category' => 'jeux-video', 'price' => 39.9, 'condition' => 'comme_neuf', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 6],
            ['vendor' => 'admin', 'title' => 'Figurine One Piece Film Red - Luffy', 'category' => 'figurines', 'price' => 27.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 5],
            ['vendor' => 'admin', 'title' => 'Funko Pop Attack on Titan - Eren', 'category' => 'figurines', 'price' => 14.9, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 9],
            ['vendor' => 'admin', 'title' => 'Statuette Dragon Ball Super - Broly', 'category' => 'figurines', 'price' => 54, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 2],
            ['vendor' => 'admin', 'title' => 'Figurine Pokémon Center - Mewtwo', 'category' => 'figurines', 'price' => 32, 'condition' => 'comme_neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 3],
            ['vendor' => 'admin', 'title' => 'Funko Pop Disney - Mickey Mouse', 'category' => 'figurines', 'price' => 12.9, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 14],
            ['vendor' => 'admin', 'title' => 'Chainsaw Man Tome 1', 'category' => 'manga', 'price' => 7.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 12],
            ['vendor' => 'admin', 'title' => 'Jujutsu Kaisen Tome 1', 'category' => 'manga', 'price' => 7.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 12],
            ['vendor' => 'admin', 'title' => 'Spy x Family Tome 1', 'category' => 'manga', 'price' => 7.5, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 10],
            ['vendor' => 'admin', 'title' => 'Berserk - Coffret Deluxe Vol.1', 'category' => 'manga', 'price' => 45, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 4],
            ['vendor' => 'admin', 'title' => 'Vinland Saga Tome 1', 'category' => 'manga', 'price' => 8.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 8],
            ['vendor' => 'admin', 'title' => 'Plaid polaire Pokémon', 'category' => 'goodies', 'price' => 22, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 8],
            ['vendor' => 'admin', 'title' => 'Lampe Pokéball veilleuse', 'category' => 'goodies', 'price' => 18.9, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 10],
            ['vendor' => 'admin', 'title' => 'Boîte de rangement pour cartes à collectionner', 'category' => 'goodies', 'price' => 9.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 20],
            ['vendor' => 'admin', 'title' => 'Album de rangement 400 cartes', 'category' => 'goodies', 'price' => 16.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 15],
            ['vendor' => 'admin', 'title' => 'Coffret goodies surprise pop culture', 'category' => 'goodies', 'price' => 29.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 10],

            // --- Catalogue complémentaire (approfondit les boutiques existantes) ---
            ['vendor' => 'leo', 'title' => 'Venusaur Holo - Base Set', 'category' => 'cartes-a-collectionner', 'price' => 38, 'condition' => 'bon_etat', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 3],
            ['vendor' => 'leo', 'title' => 'Gyarados 1ère Édition Shadowless', 'category' => 'cartes-a-collectionner', 'price' => 72, 'condition' => 'bon_etat', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 1],
            ['vendor' => 'leo', 'title' => 'Figurine Pokémon Center - Pikachu', 'category' => 'figurines', 'price' => 24.9, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => null, 'stock' => 7],
            ['vendor' => 'tck', 'title' => 'Charizard Base Set Shadowless', 'category' => 'cartes-a-collectionner', 'price' => 320, 'condition' => 'bon_etat', 'brand' => 'Pokemon', 'rarity' => 'secrete', 'stock' => 1],
            ['vendor' => 'tck', 'title' => 'Booster Star Wars Unlimited', 'category' => 'cartes-a-collectionner', 'price' => 4.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => 'commune', 'stock' => 22],
            ['vendor' => 'tck', 'title' => 'Force of Will - Carte Magic rare', 'category' => 'cartes-a-collectionner', 'price' => 11, 'condition' => 'neuf', 'brand' => null, 'rarity' => 'rare', 'stock' => 9],
            ['vendor' => 'retropixel', 'title' => 'Console Wii blanche + Wii Sports', 'category' => 'jeux-video', 'price' => 45, 'condition' => 'bon_etat', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 2],
            ['vendor' => 'retropixel', 'title' => 'Chrono Trigger - SNES', 'category' => 'jeux-video', 'price' => 95, 'condition' => 'usage', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 1],
            ['vendor' => 'retropixel', 'title' => 'Manette PS1 originale', 'category' => 'jeux-video', 'price' => 14.9, 'condition' => 'usage', 'brand' => 'PlayStation', 'rarity' => null, 'stock' => 6],
            ['vendor' => 'mangacorner', 'title' => 'Vagabond Tome 1', 'category' => 'manga', 'price' => 9.5, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 4],
            ['vendor' => 'mangacorner', 'title' => 'Tokyo Ghoul Tome 1', 'category' => 'manga', 'price' => 8, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 5],
            ['vendor' => 'popgoodies', 'title' => 'Figurine porte-clés Kirby', 'category' => 'goodies', 'price' => 5.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 18],
            ['vendor' => 'popgoodies', 'title' => 'Tapis de bain Animal Crossing', 'category' => 'goodies', 'price' => 16.5, 'condition' => 'neuf', 'brand' => 'Nintendo', 'rarity' => null, 'stock' => 6],
            ['vendor' => 'popgoodies', 'title' => 'Bougie parfumée gaming', 'category' => 'goodies', 'price' => 12.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 9],
            ['vendor' => 'funko', 'title' => 'Funko Pop Naruto Shippuden - Sasuke', 'category' => 'figurines', 'price' => 14.9, 'condition' => 'neuf', 'brand' => 'Funko', 'rarity' => null, 'stock' => 10],
            ['vendor' => 'funko', 'title' => 'Figurine articulée Dragon Ball - Vegeta', 'category' => 'figurines', 'price' => 36, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 3],
            ['vendor' => 'admin', 'title' => 'Eevee GX - Soleil et Lune', 'category' => 'cartes-a-collectionner', 'price' => 15.9, 'condition' => 'neuf', 'brand' => 'Pokemon', 'rarity' => 'holo', 'stock' => 8],
            ['vendor' => 'admin', 'title' => 'Console PSP-3000 + 5 jeux', 'category' => 'jeux-video', 'price' => 74, 'condition' => 'bon_etat', 'brand' => 'PlayStation', 'rarity' => null, 'stock' => 2],
            ['vendor' => 'admin', 'title' => 'Figurine Ichiban Kuji - Naruto Uzumaki', 'category' => 'figurines', 'price' => 31, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 4],
            ['vendor' => 'admin', 'title' => 'Bleach Tome 1', 'category' => 'manga', 'price' => 7.5, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 9],
            ['vendor' => 'admin', 'title' => 'Sticker pack pop culture (50 pièces)', 'category' => 'goodies', 'price' => 6.9, 'condition' => 'neuf', 'brand' => null, 'rarity' => null, 'stock' => 25],
            ['vendor' => 'tck', 'title' => 'Espeon VMAX Alt Art', 'category' => 'cartes-a-collectionner', 'price' => 68, 'condition' => 'comme_neuf', 'brand' => 'Pokemon', 'rarity' => 'secrete', 'stock' => 2],
            ['vendor' => 'retropixel', 'title' => 'Console Dreamcast + Sonic Adventure', 'category' => 'jeux-video', 'price' => 99, 'condition' => 'bon_etat', 'brand' => null, 'rarity' => null, 'stock' => 1],
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
                if ($realPhoto = self::IMAGE_OVERRIDES[$data['title']] ?? null) {
                    ProductImage::create(['product_id' => $product->id, 'path' => $realPhoto, 'position' => 0]);
                    ProductImage::create(['product_id' => $product->id, 'path' => $realPhoto, 'position' => 1]);
                } else {
                    $keywords = self::CATEGORY_KEYWORDS[$data['category']];
                    $keyword = $keywords[$product->id % count($keywords)];
                    // Portrait ratio for cards to match real trading-card proportions (~63x88mm); square elsewhere.
                    $dimensions = $data['category'] === 'cartes-a-collectionner' ? '560/800' : '800/800';

                    ProductImage::create(['product_id' => $product->id, 'path' => "https://loremflickr.com/{$dimensions}/{$keyword}?lock={$product->id}", 'position' => 0]);
                    ProductImage::create(['product_id' => $product->id, 'path' => "https://loremflickr.com/{$dimensions}/{$keyword}?lock=".($product->id + 1000), 'position' => 1]);
                }
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
