<div class="mt-6">
    @if ($product->stock > 0 && (! auth()->check() || auth()->user()->isAcheteur()))
        <div class="flex items-center gap-3">
            <input type="number" wire:model="quantity" min="1" max="{{ $product->stock }}"
                   class="w-20 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">

            <button wire:click="add" type="button"
                    class="flex-1 bg-violet-600 hover:bg-violet-500 text-white font-semibold px-6 py-2.5 rounded-full shadow-sm shadow-violet-600/20 hover:shadow-md transition-all">
                {{ __('Ajouter au panier') }}
            </button>
        </div>

        @if ($status)
            <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-2 font-medium">{{ $status }}</p>
        @endif
    @endif
</div>
