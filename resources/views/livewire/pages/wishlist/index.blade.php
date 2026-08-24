<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public function with(): array
    {
        return [
            'products' => auth()->user()->wishlistedProducts()
                ->with(['images', 'category', 'seller'])
                ->orderByDesc('wishlist_items.created_at')
                ->paginate(12),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Mes favoris') }}</h1>

    @if ($products->isEmpty())
        <div class="py-16 text-center">
            <div class="text-5xl mb-3">🤍</div>
            <p class="text-gray-500 dark:text-gray-400">{{ __("Vous n'avez pas encore ajouté d'article à vos favoris.") }}</p>
            <a href="{{ route('products.index') }}" wire:navigate class="inline-block mt-4 text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                {{ __('Parcourir le catalogue →') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
            @foreach ($products as $product)
                <x-product-card :product="$product" wire:key="wish-product-{{ $product->id }}" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
