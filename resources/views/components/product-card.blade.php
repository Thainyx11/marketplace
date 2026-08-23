@props(['product'])

<a href="{{ route('products.show', $product->slug) }}" wire:navigate
   class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition">
    <div class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
        @if ($product->images->first())
            <img src="{{ Storage::url($product->images->first()->path) }}" alt="{{ $product->title }}" class="object-cover w-full h-full">
        @else
            <span class="text-gray-400 text-sm">{{ __('Pas de photo') }}</span>
        @endif
    </div>
    <div class="p-3">
        <p class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $product->title }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $product->seller->shop_name ?? $product->seller->name }}</p>
        <div class="flex items-center justify-between mt-2">
            <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ number_format($product->price, 2, ',', ' ') }} €</span>
            @if ($product->stock < 1)
                <span class="text-xs text-red-500">{{ __('Épuisé') }}</span>
            @endif
        </div>
    </div>
</a>
