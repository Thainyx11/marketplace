@props(['product'])

<div class="group relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
    <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="block">
    <div class="relative {{ $product->category->slug === 'cartes-a-collectionner' ? 'aspect-[5/7]' : 'aspect-square' }} bg-gray-100 dark:bg-gray-700 overflow-hidden">
        @if ($product->images->first())
            <img src="{{ $product->images->first()->url }}" alt="{{ $product->title }}"
                 class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="flex flex-col items-center justify-center w-full h-full text-gray-300 dark:text-gray-600 gap-1.5">
                <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 8.25V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V8.25M3 8.25V6a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 6v2.25m-18 0h18M8.25 6h.008v.008H8.25V6z" />
                </svg>
                <span class="text-[11px] font-medium uppercase tracking-wide">{{ __('Photo à venir') }}</span>
            </div>
        @endif

        @if ($product->rarity)
            <x-badge color="amber" class="absolute top-2 left-2 shadow-sm">{{ ucfirst($product->rarity) }}</x-badge>
        @endif

        @if ($product->stock < 1)
            <span class="absolute inset-0 bg-gray-900/50 flex items-center justify-center">
                <span class="text-white text-xs font-bold uppercase tracking-wide bg-gray-900/70 px-3 py-1 rounded-full">{{ __('Épuisé') }}</span>
            </span>
        @endif
    </div>
    <div class="p-3.5">
        <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $product->title }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $product->seller->shop_name ?? $product->seller->name }}</p>
        <p class="font-bold text-brand-600 dark:text-brand-400 mt-2">{{ number_format($product->price, 2, ',', ' ') }} €</p>
    </div>
    </a>

    <livewire:components.wishlist-toggle :product="$product" :key="'wishlist-'.$product->id" />
</div>
