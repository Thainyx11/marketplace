<div>
    @if ($compact)
        @auth
            <button type="button" wire:click="toggle" wire:key="wishlist-btn-{{ $product->id }}"
                    title="{{ $isWishlisted ? __('Retirer des favoris') : __('Ajouter aux favoris') }}"
                    class="absolute top-2 right-2 z-10 h-8 w-8 grid place-items-center rounded-full bg-white/90 dark:bg-gray-900/80 shadow-sm hover:scale-110 transition">
                <svg class="h-4 w-4 {{ $isWishlisted ? 'fill-red-500 text-red-500' : 'fill-none text-gray-500' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>
        @else
            <a href="{{ route('login') }}" wire:navigate title="{{ __('Connectez-vous pour ajouter aux favoris') }}"
               class="absolute top-2 right-2 z-10 h-8 w-8 grid place-items-center rounded-full bg-white/90 dark:bg-gray-900/80 shadow-sm hover:scale-110 transition">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </a>
        @endauth
    @else
        @auth
            <button type="button" wire:click="toggle"
                    class="inline-flex items-center gap-2 mt-3 text-sm font-semibold {{ $isWishlisted ? 'text-red-500' : 'text-gray-600 dark:text-gray-400' }} hover:underline">
                <svg class="h-4 w-4 {{ $isWishlisted ? 'fill-red-500 text-red-500' : 'fill-none text-gray-400' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                {{ $isWishlisted ? __('Dans mes favoris') : __('Ajouter aux favoris') }}
            </button>
        @else
            <a href="{{ route('login') }}" wire:navigate class="inline-flex items-center gap-2 mt-3 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:underline">
                <svg class="h-4 w-4 fill-none text-gray-400" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                {{ __('Connectez-vous pour ajouter aux favoris') }}
            </a>
        @endauth
    @endif
</div>
