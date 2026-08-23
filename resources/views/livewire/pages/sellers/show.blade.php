<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public User $seller;

    public function mount(string $shop_slug): void
    {
        $seller = User::where('role', 'vendeur')->where('shop_slug', $shop_slug)->first();

        abort_if(! $seller, 404);

        $this->seller = $seller;
    }

    public function with(): array
    {
        return [
            'products' => $this->seller->products()->active()->with('images')->latest()->paginate(12),
            'averageRating' => $this->seller->averageRating(),
            'reviewsCount' => \App\Models\Review::whereHas('product', fn ($q) => $q->where('user_id', $this->seller->id))->count(),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $seller->shop_name ?? $seller->name }}</h1>

        @if ($averageRating)
            <p class="text-amber-500 mt-1">★ {{ number_format($averageRating, 1) }} <span class="text-gray-500 dark:text-gray-400 text-sm">({{ __(':count avis', ['count' => $reviewsCount]) }})</span></p>
        @endif

        @if ($seller->bio)
            <p class="text-gray-700 dark:text-gray-300 mt-3">{{ $seller->bio }}</p>
        @endif
    </div>

    <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Produits en vente') }}</h2>

    @if ($products->isEmpty())
        <p class="text-gray-500 dark:text-gray-400 py-8">{{ __("Cette boutique n'a aucun produit en vente actuellement.") }}</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($products as $product)
                <x-product-card :product="$product" wire:key="seller-product-{{ $product->id }}" />
            @endforeach
        </div>

        <div class="mt-8">{{ $products->links() }}</div>
    @endif
</div>
