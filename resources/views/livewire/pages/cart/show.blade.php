<?php

use App\Models\Product;
use App\Services\CartManager;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function updateQuantity(int $productId, int $quantity): void
    {
        $product = Product::findOrFail($productId);
        $this->manager()->updateQuantity($product, $quantity);
    }

    public function remove(int $productId): void
    {
        $this->manager()->remove(Product::findOrFail($productId));
    }

    public function checkout(): mixed
    {
        if (! auth()->check()) {
            return $this->redirect(route('login'), navigate: true);
        }

        if (! auth()->user()->isAcheteur()) {
            session()->flash('error', __('Seuls les comptes acheteur peuvent passer commande.'));

            return null;
        }

        if ($this->manager()->items()->isEmpty()) {
            session()->flash('error', __('Votre panier est vide.'));

            return null;
        }

        return $this->redirect(route('checkout.show'), navigate: true);
    }

    private function manager(): CartManager
    {
        return new CartManager(auth()->user());
    }

    public function with(): array
    {
        return [
            'items' => $this->manager()->items(),
            'total' => $this->manager()->total(),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Mon panier') }}</h1>

    @if ($items->isEmpty())
        <div class="py-16 text-center">
            <div class="text-5xl mb-3">🛒</div>
            <p class="text-gray-500 dark:text-gray-400">{{ __('Votre panier est vide.') }}</p>
            <a href="{{ route('products.index') }}" wire:navigate class="inline-block mt-4 text-sm font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                {{ __('Parcourir le catalogue →') }}
            </a>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($items as $entry)
                @php $product = $entry['product']; @endphp
                <div class="flex items-center gap-4 p-4" wire:key="cart-item-{{ $product->id }}">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden shrink-0 flex items-center justify-center">
                        @if ($product->images->first())
                            <img src="{{ Storage::url($product->images->first()->path) }}" class="object-cover w-full h-full">
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="font-semibold text-gray-900 dark:text-gray-100 hover:text-violet-600 dark:hover:text-violet-400 truncate block">
                            {{ $product->title }}
                        </a>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($product->price, 2, ',', ' ') }} € {{ __('/ unité') }}</p>
                    </div>

                    <input type="number" min="1" max="{{ $product->stock }}" value="{{ $entry['quantity'] }}"
                           wire:change="updateQuantity({{ $product->id }}, $event.target.value)"
                           class="w-20 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">

                    <span class="font-bold text-gray-900 dark:text-gray-100 w-24 text-right">
                        {{ number_format($product->price * $entry['quantity'], 2, ',', ' ') }} €
                    </span>

                    <button type="button" wire:click="remove({{ $product->id }})" class="text-gray-400 hover:text-red-500 transition" title="{{ __('Retirer') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5">
            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Total') }} : <span class="text-violet-600 dark:text-violet-400">{{ number_format($total, 2, ',', ' ') }} €</span></span>

            <x-primary-button wire:click="checkout" type="button" class="px-8 py-3">
                {{ __('Passer commande') }}
            </x-primary-button>
        </div>
    @endif
</div>
