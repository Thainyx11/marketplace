<?php

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function remove(Product $product): void
    {
        $product->update(['status' => 'removed']);
    }

    public function restore(Product $product): void
    {
        $product->update(['status' => 'active']);
    }

    public function destroy(Product $product): void
    {
        foreach ($product->images as $image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
        }

        $product->delete();
    }

    public function with(): array
    {
        $query = Product::with('seller');

        if ($this->search !== '') {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$this->search}%")->orWhereHas('seller', fn ($s) => $s->where('name', 'like', "%{$this->search}%")));
        }

        return ['products' => $query->latest()->paginate(15)];
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Titre ou vendeur...') }}"
           class="w-full max-w-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm mb-4 focus:border-violet-500 focus:ring-violet-500">

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @foreach ($products as $product)
            <div class="flex items-center gap-4 p-4" wire:key="admin-product-{{ $product->id }}">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $product->title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->seller->name }} · {{ number_format($product->price, 2, ',', ' ') }} €</p>
                </div>

                <x-badge :color="match($product->status) { 'active' => 'emerald', 'hidden' => 'gray', 'removed' => 'red' }">
                    {{ ['active' => 'En vente', 'hidden' => 'Masqué', 'removed' => 'Retiré'][$product->status] }}
                </x-badge>

                @if ($product->status === 'removed')
                    <button type="button" wire:click="restore({{ $product->id }})" class="text-sm font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                        {{ __('Restaurer') }}
                    </button>
                @else
                    <button type="button" wire:click="remove({{ $product->id }})" wire:confirm="{{ __('Retirer ce produit (fraude / contenu inapproprié) ?') }}"
                            class="text-sm text-amber-600 hover:underline">
                        {{ __('Retirer') }}
                    </button>
                @endif

                <button type="button" wire:click="destroy({{ $product->id }})" wire:confirm="{{ __('Supprimer définitivement ? Cette action est irréversible.') }}"
                        class="text-sm text-red-500 hover:underline">
                    {{ __('Supprimer') }}
                </button>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
</div>
