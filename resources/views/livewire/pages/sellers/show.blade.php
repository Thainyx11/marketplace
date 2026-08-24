<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public User $seller;

    public function mount(string $shop_slug): void
    {
        $seller = User::whereIn('role', ['vendeur', 'admin'])->where('shop_slug', $shop_slug)->first();

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

<div>
    <div class="h-28 sm:h-36 bg-gradient-to-br from-violet-600 via-fuchsia-600 to-amber-500"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 -mt-14 relative mb-8">
            <div class="flex items-end gap-4">
                <span class="grid place-items-center h-20 w-20 rounded-2xl bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white font-extrabold text-3xl shrink-0 ring-4 ring-white dark:ring-gray-800 -mt-2">
                    {{ Str::upper(Str::substr($seller->shop_name ?? $seller->name, 0, 1)) }}
                </span>
                <div class="min-w-0 pb-1">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-gray-100 truncate">{{ $seller->shop_name ?? $seller->name }}</h1>
                    @if ($averageRating)
                        <p class="text-amber-500 text-sm font-medium">★ {{ number_format($averageRating, 1) }} <span class="text-gray-500 dark:text-gray-400">({{ __(':count avis', ['count' => $reviewsCount]) }})</span></p>
                    @endif
                </div>
            </div>

            @if ($seller->bio)
                <p class="text-gray-700 dark:text-gray-300 mt-4">{{ $seller->bio }}</p>
            @endif
        </div>

        <div class="pb-16">
            <h2 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-4">{{ __('Produits en vente') }}</h2>

            @if ($products->isEmpty())
                <div class="py-12 text-center">
                    <div class="text-5xl mb-3">📦</div>
                    <p class="text-gray-500 dark:text-gray-400">{{ __("Cette boutique n'a aucun produit en vente actuellement.") }}</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" wire:key="seller-product-{{ $product->id }}" />
                    @endforeach
                </div>

                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>
