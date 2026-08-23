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
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Mon panier') }}</h1>

    @if (session('error'))
        <div class="bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-lg p-4 text-sm mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if ($items->isEmpty())
        <p class="text-gray-500 dark:text-gray-400 py-12 text-center">
            {{ __('Votre panier est vide.') }}
            <a href="{{ route('products.index') }}" wire:navigate class="underline text-indigo-600 dark:text-indigo-400">{{ __('Parcourir le catalogue') }}</a>
        </p>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($items as $entry)
                @php $product = $entry['product']; @endphp
                <div class="flex items-center gap-4 p-4" wire:key="cart-item-{{ $product->id }}">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded overflow-hidden shrink-0 flex items-center justify-center">
                        @if ($product->images->first())
                            <img src="{{ Storage::url($product->images->first()->path) }}" class="object-cover w-full h-full">
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="font-medium text-gray-900 dark:text-gray-100 hover:underline truncate block">
                            {{ $product->title }}
                        </a>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($product->price, 2, ',', ' ') }} € {{ __('/ unité') }}</p>
                    </div>

                    <input type="number" min="1" max="{{ $product->stock }}" value="{{ $entry['quantity'] }}"
                           wire:change="updateQuantity({{ $product->id }}, $event.target.value)"
                           class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">

                    <span class="font-semibold text-gray-900 dark:text-gray-100 w-24 text-right">
                        {{ number_format($product->price * $entry['quantity'], 2, ',', ' ') }} €
                    </span>

                    <button type="button" wire:click="remove({{ $product->id }})" class="text-sm text-red-500 hover:underline">
                        {{ __('Retirer') }}
                    </button>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between mt-6">
            <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Total') }} : {{ number_format($total, 2, ',', ' ') }} €</span>

            <button type="button" wire:click="checkout" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-lg">
                {{ __('Passer commande') }}
            </button>
        </div>
    @endif
</div>
