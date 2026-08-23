<?php

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        Gate::authorize('create', Product::class);
    }

    public function delete(Product $product): void
    {
        Gate::authorize('delete', $product);

        foreach ($product->images as $image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
        }

        $product->delete();
        session()->flash('status', __('Produit supprimé.'));
    }

    public function with(): array
    {
        return [
            'products' => auth()->user()->products()->with('images')->latest()->paginate(10),
        ];
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('Mes produits') }}</h1>
        <a href="{{ route('vendor.products.create') }}" wire:navigate
           class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2 rounded-lg text-sm">
            {{ __('+ Nouveau produit') }}
        </a>
    </div>

    @if (session('status'))
        <div class="bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg p-4 text-sm mb-6">
            {{ session('status') }}
        </div>
    @endif

    @if (! auth()->user()->is_approved)
        <div class="bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 rounded-lg p-4 text-sm mb-6">
            {{ __('Votre compte vendeur doit être approuvé par un administrateur avant de pouvoir publier des produits.') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($products as $product)
            <div class="flex items-center gap-4 p-4" wire:key="product-{{ $product->id }}">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded overflow-hidden shrink-0 flex items-center justify-center">
                    @if ($product->images->first())
                        <img src="{{ Storage::url($product->images->first()->path) }}" class="object-cover w-full h-full">
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $product->title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ number_format($product->price, 2, ',', ' ') }} € · {{ __('Stock') }} : {{ $product->stock }}
                        · <span @class(['text-green-600' => $product->status === 'active', 'text-gray-400' => $product->status !== 'active'])>
                            {{ ['active' => 'En vente', 'hidden' => 'Masqué', 'removed' => 'Retiré'][$product->status] }}
                        </span>
                    </p>
                </div>

                <a href="{{ route('vendor.products.edit', $product) }}" wire:navigate class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('Modifier') }}
                </a>

                <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="{{ __('Supprimer ce produit ?') }}"
                        class="text-sm text-red-500 hover:underline">
                    {{ __('Supprimer') }}
                </button>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 p-8 text-center">{{ __("Vous n'avez pas encore de produit en vente.") }}</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
</div>
