<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        return [
            'categories' => Category::withCount(['products' => fn ($q) => $q->active()])->get(),
            'latestProducts' => Product::active()->with(['images', 'seller'])->latest()->take(8)->get(),
        ];
    }
}; ?>

<div>
    <div class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold">{{ __('La marketplace des passionnés de pop culture') }}</h1>
            <p class="mt-4 text-gray-300 max-w-2xl mx-auto">
                {{ __('Cartes à collectionner, jeux vidéo rétro, figurines, manga et goodies — achetez et vendez entre particuliers en toute confiance.') }}
            </p>
            <a href="{{ route('products.index') }}" wire:navigate
               class="inline-block mt-6 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-lg">
                {{ __('Parcourir le catalogue') }}
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Catégories') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach ($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" wire:navigate
                   class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 text-center hover:shadow-md transition">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $category->products_count }} {{ __('articles') }}</p>
                </a>
            @endforeach
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Nouveautés') }}</h2>

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
