<div class="mt-6">
    @if ($product->stock > 0 && (! auth()->check() || auth()->user()->isAcheteur()))
        <div class="flex items-center gap-3">
            <input type="number" wire:model="quantity" min="1" max="{{ $product->stock }}"
                   class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">

            <button wire:click="add" type="button"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg">
                {{ __('Ajouter au panier') }}
            </button>
        </div>

        @if ($status)
            <p class="text-sm text-green-600 dark:text-green-400 mt-2">{{ $status }}</p>
        @endif
    @endif
</div>
