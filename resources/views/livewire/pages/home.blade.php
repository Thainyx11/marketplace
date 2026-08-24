<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        $popular = Product::active()->with(['images', 'seller', 'category'])
            ->withCount('orderItems')->orderByDesc('order_items_count')->take(3)->get();

        $latest = Product::active()->with(['images', 'seller', 'category'])->latest()->take(4)->get();

        return [
            'categories' => Category::withCount(['products' => fn ($q) => $q->active()])->whereNull('parent_id')->get(),
            'heroProducts' => $popular->concat($latest)->unique('id')->take(6)->values(),
            'latestProducts' => $latest,
            'productsCount' => Product::active()->count(),
            'vendorsCount' => User::whereIn('role', ['vendeur', 'admin'])->where('is_approved', true)->whereNotNull('shop_slug')->count(),
        ];
    }
}; ?>

<div>
    <div class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-gray-950 to-gray-900 text-white">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #5082F1 0, transparent 35%), radial-gradient(circle at 80% 60%, #012169 0, transparent 30%);"></div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 sm:pt-20 pb-10 text-center">
            <span class="inline-flex items-center gap-1.5 bg-white/10 text-brand-200 text-xs font-semibold px-3 py-1 rounded-full mb-6">
                ✨ {{ __('Cartes, jeux rétro, figurines & plus') }}
            </span>

            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">
                {{ __('La marketplace des passionnés de') }} <span class="bg-gradient-to-r from-brand-300 to-brand-500 bg-clip-text text-transparent">{{ __('pop culture') }}</span>
            </h1>
            <p class="mt-5 text-gray-300 max-w-xl mx-auto">
                {{ __('Achetez et vendez entre particuliers en toute confiance : cartes à collectionner, jeux vidéo rétro, figurines, manga et goodies.') }}
            </p>

            <form action="{{ route('products.index') }}" method="GET" class="mt-8 max-w-lg mx-auto">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M18 10.5a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                    </svg>
                    <input type="text" name="q" placeholder="{{ __('Rechercher un Charizard, une Switch...') }}"
                           class="w-full bg-white text-gray-900 placeholder-gray-400 rounded-full pl-12 pr-32 py-4 shadow-xl focus:ring-2 focus:ring-brand-400 border-transparent">
                    <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 bg-brand-800 hover:bg-brand-700 text-white font-semibold px-5 rounded-full transition">
                        {{ __('Rechercher') }}
                    </button>
                </div>
            </form>

            <div class="mt-8 flex items-center justify-center gap-8 text-sm text-gray-400">
                <span><span class="text-white font-bold">{{ $productsCount }}</span> {{ __('articles en vente') }}</span>
                <span class="h-1 w-1 rounded-full bg-gray-600"></span>
                <span><span class="text-white font-bold">{{ $vendorsCount }}</span> {{ __('vendeurs') }}</span>
            </div>
        </div>

        @if ($heroProducts->isNotEmpty())
            <div class="relative px-4 sm:px-6 lg:px-8 pb-16">
                <x-carousel :dark="true" :interval="5500">
                    @foreach ($heroProducts as $index => $product)
                        <x-carousel-slide>
                            <a href="{{ route('products.show', $product->slug) }}" wire:navigate
                               class="group relative block aspect-[4/3] sm:aspect-[21/9] rounded-2xl overflow-hidden">
                                @if ($product->images->first())
                                    <img src="{{ $product->images->first()->url }}" alt="{{ $product->title }}"
                                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/5"></div>

                                <div class="absolute inset-0 flex flex-col justify-end p-6 sm:p-10">
                                    <span class="inline-flex items-center gap-1 self-start bg-gradient-to-r from-brand-700 to-brand-900 text-white text-xs font-bold px-3 py-1 rounded-full">
                                        {{ $index < 3 && $product->order_items_count > 0 ? '🔥 '.__('Populaire') : '🆕 '.__('Nouveauté') }}
                                    </span>
                                    <h2 class="text-2xl sm:text-4xl font-extrabold mt-3 max-w-2xl">{{ $product->title }}</h2>
                                    <p class="text-gray-300 text-sm sm:text-base mt-1">{{ $product->seller->shop_name ?? $product->seller->name }}</p>
                                    <div class="flex items-center gap-4 mt-4">
                                        <p class="text-2xl sm:text-3xl font-extrabold bg-gradient-to-r from-brand-300 to-brand-500 bg-clip-text text-transparent">
                                            {{ number_format($product->price, 2, ',', ' ') }} €
                                        </p>
                                        <span class="bg-white text-gray-900 font-semibold px-5 py-2.5 rounded-full text-sm">
                                            {{ __('Voir le produit →') }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </x-carousel-slide>
                    @endforeach
                </x-carousel>
            </div>
        @endif
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-5">{{ __('Catégories') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @php
                $icons = [
                    'cartes-a-collectionner' => '🃏',
                    'jeux-video' => '🎮',
                    'figurines' => '🧸',
                    'manga' => '📚',
                    'goodies' => '🎁',
                ];
            @endphp
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['categorie' => $category->slug]) }}" wire:navigate
                   class="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 text-center hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <span class="text-3xl">{{ $icons[$category->slug] ?? '📦' }}</span>
                    <p class="font-semibold text-gray-900 dark:text-gray-100 mt-2 group-hover:text-brand-600 dark:group-hover:text-brand-400 transition">{{ $category->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $category->products_count }} {{ __('articles') }}</p>
                </a>
            @endforeach
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Nouveautés') }}</h2>
            <a href="{{ route('products.index') }}" wire:navigate class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                {{ __('Voir tout →') }}
            </a>
        </div>

        @if ($latestProducts->isEmpty())
            <p class="text-gray-500 dark:text-gray-400">{{ __('Aucun produit pour le moment.') }}</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($latestProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 text-center mb-8">{{ __('Comment ça marche ?') }}</h2>

            <x-carousel :interval="7000">
                <x-carousel-slide>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 text-center">
                            <span class="text-3xl">🔍</span>
                            <p class="font-bold text-gray-900 dark:text-gray-100 mt-3">1. {{ __('Parcourez') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Filtrez par catégorie, marque, état ou rareté pour trouver la pièce qui vous manque.') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 text-center">
                            <span class="text-3xl">💳</span>
                            <p class="font-bold text-gray-900 dark:text-gray-100 mt-3">2. {{ __('Achetez en sécurité') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Paiement protégé par Stripe : aucune donnée bancaire ne transite par nos serveurs.') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 text-center">
                            <span class="text-3xl">⭐</span>
                            <p class="font-bold text-gray-900 dark:text-gray-100 mt-3">3. {{ __('Recevez et notez') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Suivez votre livraison puis laissez un avis pour aider la communauté.') }}</p>
                        </div>
                    </div>
                    <p class="text-center text-sm font-semibold text-brand-600 dark:text-brand-400 mt-6">{{ __('Pour les acheteurs') }}</p>
                </x-carousel-slide>

                <x-carousel-slide>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 text-center">
                            <span class="text-3xl">🏪</span>
                            <p class="font-bold text-gray-900 dark:text-gray-100 mt-3">1. {{ __('Créez votre boutique') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __("Inscrivez-vous en tant que vendeur — votre compte est validé rapidement par l'équipe.") }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 text-center">
                            <span class="text-3xl">📸</span>
                            <p class="font-bold text-gray-900 dark:text-gray-100 mt-3">2. {{ __('Publiez vos articles') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Photos, état, prix, rareté : décrivez vos objets en quelques minutes.') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 text-center">
                            <span class="text-3xl">💶</span>
                            <p class="font-bold text-gray-900 dark:text-gray-100 mt-3">3. {{ __('Encaissez vos ventes') }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Une commission raisonnable est prélevée par vente, le reste est à vous.') }}</p>
                        </div>
                    </div>
                    <p class="text-center text-sm font-semibold text-brand-600 dark:text-brand-400 mt-6">{{ __('Pour les vendeurs') }}</p>
                </x-carousel-slide>
            </x-carousel>
        </div>
    </div>
</div>
