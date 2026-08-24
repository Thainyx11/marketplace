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
        <a href="{{ route('products.index') }}" wire:navigate class="hover:text-violet-600 dark:hover:text-violet-400">{{ __('Catalogue') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ route('products.index', ['categorie' => $product->category->slug]) }}" wire:navigate class="hover:text-violet-600 dark:hover:text-violet-400">{{ $product->category->name }}</a>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div x-data="{ active: 0, images: {{ $product->images->map(fn ($i) => $i->url)->toJson() }} }">
            <div class="relative aspect-square bg-gray-100 dark:bg-gray-800 rounded-2xl overflow-hidden flex items-center justify-center">
                <template x-if="images.length">
                    <img :src="images[active]" alt="{{ $product->title }}" class="object-cover w-full h-full">
                </template>
                @if ($product->images->isEmpty())
                    <svg class="h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 8.25V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V8.25M3 8.25V6a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 6v2.25m-18 0h18M8.25 6h.008v.008H8.25V6z" />
                    </svg>
                @endif

                @if ($product->rarity)
                    <x-badge color="amber" class="absolute top-3 left-3">{{ ucfirst($product->rarity) }}</x-badge>
                @endif
            </div>

            @if ($product->images->count() > 1)
                <div class="grid grid-cols-5 gap-2 mt-2">
                    @foreach ($product->images as $index => $image)
                        <button type="button" @click="active = {{ $index }}"
                                :class="active === {{ $index }} ? 'ring-2 ring-violet-500' : 'ring-1 ring-transparent opacity-80 hover:opacity-100'"
                                class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden transition">
                            <img src="{{ $image->url }}" class="object-cover w-full h-full">
                        </button>
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

            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-gray-100">{{ $product->title }}</h1>

            <div class="flex flex-wrap items-center gap-2 mt-3">
                @if ($product->brand)
                    <x-badge color="gray">{{ $product->brand }}</x-badge>
                @endif
                <x-badge color="violet">{{ ['neuf' => 'Neuf', 'comme_neuf' => 'Comme neuf', 'bon_etat' => 'Bon état', 'usage' => 'Usagé'][$product->condition] }}</x-badge>
            </div>

            <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5">
                <p class="text-3xl font-extrabold text-violet-600 dark:text-violet-400">{{ number_format($product->price, 2, ',', ' ') }} €</p>

                <p class="text-sm mt-1 font-medium {{ $product->stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                    {{ $product->stock > 0 ? __(':count en stock', ['count' => $product->stock]) : __('Épuisé') }}
                </p>

                <livewire:components.add-to-cart :product="$product" />
            </div>

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-2">{{ __('Description') }}</h2>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $product->description }}</p>
            </div>

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Vendeur') }}</h2>
                <a href="{{ route('sellers.show', $product->seller->shop_slug ?? $product->seller->id) }}" wire:navigate
                   class="flex items-center gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition">
                    <span class="grid place-items-center h-11 w-11 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white font-bold shrink-0">
                        {{ Str::upper(Str::substr($product->seller->shop_name ?? $product->seller->name, 0, 1)) }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $product->seller->shop_name ?? $product->seller->name }}</p>
                        @if ($product->seller->averageRating())
                            <p class="text-sm text-amber-500">★ {{ number_format($product->seller->averageRating(), 1) }}</p>
                        @endif
                    </div>
                    <span class="text-sm font-semibold text-violet-600 dark:text-violet-400 shrink-0">{{ __('Voir la boutique →') }}</span>
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
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Avis') }}</h2>
                    <div class="space-y-3">
                        @foreach ($product->reviews as $review)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm">
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
