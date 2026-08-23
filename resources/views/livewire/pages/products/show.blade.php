<?php

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        Gate::authorize('view', $product);

        $this->product = $product->load(['images', 'seller', 'category', 'reviews' => fn ($q) => $q->latest()]);
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        <a href="{{ route('products.index') }}" wire:navigate class="hover:underline">{{ __('Catalogue') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ route('products.index', ['categorie' => $product->category->slug]) }}" wire:navigate class="hover:underline">{{ $product->category->name }}</a>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div>
            <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden flex items-center justify-center">
                @if ($product->images->first())
                    <img src="{{ Storage::url($product->images->first()->path) }}" alt="{{ $product->title }}" class="object-cover w-full h-full">
                @else
                    <span class="text-gray-400">{{ __('Pas de photo') }}</span>
                @endif
            </div>

            @if ($product->images->count() > 1)
                <div class="grid grid-cols-5 gap-2 mt-2">
                    @foreach ($product->images->skip(1) as $image)
                        <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded overflow-hidden">
                            <img src="{{ Storage::url($image->path) }}" class="object-cover w-full h-full">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if ($product->status !== 'active')
                <div class="mb-4 bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 rounded-lg p-3 text-sm">
                    {{ __('Cette annonce est actuellement :status.', ['status' => $product->status === 'hidden' ? 'masquée' : 'supprimée']) }}
                </div>
            @endif

            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $product->title }}</h1>

            <div class="flex items-center gap-2 mt-2 text-sm text-gray-500 dark:text-gray-400">
                @if ($product->brand)<span>{{ $product->brand }}</span> · @endif
                <span>{{ ['neuf' => 'Neuf', 'comme_neuf' => 'Comme neuf', 'bon_etat' => 'Bon état', 'usage' => 'Usagé'][$product->condition] }}</span>
                @if ($product->rarity) · <span>{{ ucfirst($product->rarity) }}</span> @endif
            </div>

            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-4">{{ number_format($product->price, 2, ',', ' ') }} €</p>

            <p class="text-sm mt-1 {{ $product->stock > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                {{ $product->stock > 0 ? __(':count en stock', ['count' => $product->stock]) : __('Épuisé') }}
            </p>

            <livewire:components.add-to-cart :product="$product" />

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('Description') }}</h2>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $product->description }}</p>
            </div>

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Vendeur') }}</h2>
                <a href="{{ route('sellers.show', $product->seller->shop_slug ?? $product->seller->id) }}" wire:navigate
                   class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm hover:shadow-md transition">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $product->seller->shop_name ?? $product->seller->name }}</p>
                        @if ($product->seller->averageRating())
                            <p class="text-sm text-amber-500">★ {{ number_format($product->seller->averageRating(), 1) }}</p>
                        @endif
                    </div>
                    <span class="text-sm text-indigo-600 dark:text-indigo-400">{{ __('Voir la boutique →') }}</span>
                </a>

                @auth
                    @if (auth()->id() !== $product->user_id)
                        <livewire:components.contact-seller :product="$product" />
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                        <a href="{{ route('login') }}" wire:navigate class="underline">{{ __('Connectez-vous') }}</a>
                        {{ __('pour contacter le vendeur.') }}
                    </p>
                @endauth
            </div>

            @if ($product->reviews->isNotEmpty())
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('Avis') }}</h2>
                    <div class="space-y-4">
                        @foreach ($product->reviews as $review)
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                                <p class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                                @if ($review->comment)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
