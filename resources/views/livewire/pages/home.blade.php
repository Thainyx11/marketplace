<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $search = '';

    public function with(): array
    {
        return [
            'categories' => Category::withCount(['products' => fn ($q) => $q->active()])->whereNull('parent_id')->get(),
            'latestProducts' => Product::active()->with(['images', 'seller'])->latest()->take(8)->get(),
            'productsCount' => Product::active()->count(),
            'vendorsCount' => User::where('role', 'vendeur')->where('is_approved', true)->count(),
        ];
    }
}; ?>

<div>
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-950 via-gray-950 to-gray-900 text-white">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #a855f7 0, transparent 35%), radial-gradient(circle at 80% 60%, #f59e0b 0, transparent 30%);"></div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
            <span class="inline-flex items-center gap-1.5 bg-white/10 text-violet-200 text-xs font-semibold px-3 py-1 rounded-full mb-6">
                ✨ {{ __('Cartes, jeux rétro, figurines & plus') }}
            </span>

            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">
                {{ __('La marketplace des passionnés de') }} <span class="bg-gradient-to-r from-violet-400 to-amber-400 bg-clip-text text-transparent">{{ __('pop culture') }}</span>
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
                           class="w-full bg-white text-gray-900 placeholder-gray-400 rounded-full pl-12 pr-32 py-4 shadow-xl focus:ring-2 focus:ring-violet-400 border-transparent">
                    <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 bg-violet-600 hover:bg-violet-500 text-white font-semibold px-5 rounded-full transition">
                        {{ __('Rechercher') }}
                    </button>
                </div>
            </form>

            <div class="mt-10 flex items-center justify-center gap-8 text-sm text-gray-400">
                <span><span class="text-white font-bold">{{ $productsCount }}</span> {{ __('articles en vente') }}</span>
                <span class="h-1 w-1 rounded-full bg-gray-600"></span>
                <span><span class="text-white font-bold">{{ $vendorsCount }}</span> {{ __('vendeurs') }}</span>
            </div>
        </div>
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
                    <p class="font-semibold text-gray-900 dark:text-gray-100 mt-2 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition">{{ $category->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $category->products_count }} {{ __('articles') }}</p>
                </a>
            @endforeach
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Nouveautés') }}</h2>
            <a href="{{ route('products.index') }}" wire:navigate class="text-sm font-semibold text-violet-600 dark:text-violet-400 hover:underline">
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
</div>
