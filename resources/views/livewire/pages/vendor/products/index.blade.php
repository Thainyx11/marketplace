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

        // FIX: order_items.product_id is a RESTRICT foreign key (a product
        // that has ever been sold must keep its history) — deleting a sold
        // product used to throw an uncaught QueryException, surfacing as a
        // raw 500. Images were also deleted from disk *before* the DB delete
        // was even attempted, so a failed delete left orphaned ProductImage
        // rows pointing at files that no longer existed. Load the images
        // first, only touch disk once the DB delete has actually succeeded.
        $images = $product->images()->get();

        try {
            $product->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            // FIX: this friendly error explaining *why* the delete was
            // refused never actually reached the vendor — no redirect
            // follows, so it's a pure Livewire AJAX action, and
            // session()->flash() alone never reaches the user (see
            // resources/views/components/flash-messages.blade.php). The
            // vendor just saw the delete silently do nothing.
            $message = __('Ce produit a déjà été vendu et ne peut pas être supprimé — vous pouvez le masquer à la place en le modifiant.');
            session()->flash('error', $message);
            $this->dispatch('flash-message', message: $message, type: 'error');

            return;
        }

        foreach ($images as $image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($image->path);
        }

        $message = __('Produit supprimé.');
        session()->flash('status', $message);
        $this->dispatch('flash-message', message: $message, type: 'status');
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
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ __('Mes produits') }}</h1>
        <a href="{{ route('vendor.products.create') }}" wire:navigate
           class="bg-brand-800 hover:bg-brand-700 text-white font-semibold px-4 py-2.5 rounded-full text-sm transition">
            {{ __('+ Nouveau produit') }}
        </a>
    </div>

    @if (! auth()->user()->is_approved)
        <div class="bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 rounded-2xl p-4 text-sm mb-6">
            {{ __('Votre compte vendeur doit être approuvé par un administrateur avant de pouvoir publier des produits.') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($products as $product)
            <div class="flex items-center gap-4 p-4" wire:key="product-{{ $product->id }}">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-xl overflow-hidden shrink-0 flex items-center justify-center">
                    @if ($product->images->first())
                        <img src="{{ $product->images->first()->url }}" class="object-cover w-full h-full">
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $product->title }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ number_format($product->price, 2, ',', ' ') }} € · {{ __('Stock') }} : {{ $product->stock }}
                    </p>
                </div>

                <x-badge :color="$product->status === 'active' ? 'emerald' : 'gray'" class="shrink-0">
                    {{ ['active' => 'En vente', 'hidden' => 'Masqué', 'removed' => 'Retiré'][$product->status] }}
                </x-badge>

                <a href="{{ route('vendor.products.edit', $product) }}" wire:navigate class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline shrink-0">
                    {{ __('Modifier') }}
                </a>

                <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="{{ __('Supprimer ce produit ?') }}"
                        class="text-sm text-red-500 hover:underline shrink-0">
                    {{ __('Supprimer') }}
                </button>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="text-5xl mb-3">🏷️</div>
                <p class="text-gray-500 dark:text-gray-400">{{ __("Vous n'avez pas encore de produit en vente.") }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
</div>
