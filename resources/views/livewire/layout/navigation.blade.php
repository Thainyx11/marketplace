<?php

use App\Livewire\Actions\Logout;
use App\Services\CartManager;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    public int $cartCount = 0;

    public int $wishlistCount = 0;

    public function mount(): void
    {
        $this->cartCount = (new CartManager(auth()->user()))->count();
        $this->wishlistCount = auth()->check() ? auth()->user()->wishlistedProducts()->count() : 0;
    }

    #[On('cart-updated')]
    public function refreshCartCount(): void
    {
        $this->cartCount = (new CartManager(auth()->user()))->count();
    }

    #[On('wishlist-updated')]
    public function refreshWishlistCount(): void
    {
        $this->wishlistCount = auth()->check() ? auth()->user()->wishlistedProducts()->count() : 0;
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-gray-950/95 backdrop-blur supports-[backdrop-filter]:bg-gray-950/80 border-b border-white/10">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 gap-4">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <a href="{{ route('home') }}" wire:navigate class="shrink-0">
                    <x-application-logo :light="true" />
                </a>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 lg:flex">
                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" wire:navigate>
                        {{ __('Produits') }}
                    </x-nav-link>
                    <x-nav-link :href="route('sellers.index')" :active="request()->routeIs('sellers.*')" wire:navigate>
                        {{ __('Vendeurs') }}
                    </x-nav-link>
                    <x-nav-link :href="route('wanted.index')" :active="request()->routeIs('wanted.*')" wire:navigate>
                        {{ __('Recherches') }}
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                            {{ __('Tableau de bord') }}
                        </x-nav-link>
                        @if (auth()->user()->isVendeur() || auth()->user()->isAdmin())
                            <x-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')" wire:navigate>
                                {{ auth()->user()->isAdmin() ? __('Ma boutique') : __('Espace vendeur') }}
                            </x-nav-link>
                        @endif
                        <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')" wire:navigate>
                            {{ __('Messages') }}
                        </x-nav-link>
                        @if (auth()->user()->isAdmin() && Route::has('admin.dashboard'))
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')" wire:navigate>
                                {{ __('Administration') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Search -->
            <form action="{{ route('products.index') }}" method="GET" class="hidden md:flex flex-1 max-w-sm">
                <div class="relative w-full">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M18 10.5a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                    </svg>
                    <input type="text" name="q" placeholder="{{ __('Rechercher un article...') }}"
                           class="w-full bg-white/10 border-transparent rounded-full text-sm text-white placeholder-gray-400 pl-9 pr-4 py-2 focus:bg-white focus:text-gray-900 focus:placeholder-gray-400 focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                </div>
            </form>

            <!-- Right side -->
            <div class="hidden lg:flex lg:items-center gap-1 shrink-0">
                <x-theme-toggle />

                @auth
                    <a href="{{ route('wishlist.index') }}" wire:navigate class="relative inline-flex items-center justify-center h-9 w-9 rounded-full text-gray-300 hover:text-white hover:bg-white/10 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        @if ($wishlistCount)
                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center bg-brand-600 text-white text-[10px] font-bold rounded-full h-4 w-4">{{ $wishlistCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('cart.show') }}" wire:navigate class="relative inline-flex items-center justify-center h-9 w-9 rounded-full text-gray-300 hover:text-white hover:bg-white/10 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.87-4.594 2.25-6.75H5.106M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        @if ($cartCount)
                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center bg-brand-600 text-white text-[10px] font-bold rounded-full h-4 w-4">{{ $cartCount }}</span>
                        @endif
                    </a>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="ms-1 inline-flex items-center justify-center h-9 w-9 rounded-full bg-white/10 hover:bg-white/20 text-white font-semibold text-sm transition focus:outline-none">
                                {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email }}</p>
                            </div>

                            <x-dropdown-link :href="route('profile')" wire:navigate>
                                {{ __('Profil') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    {{ __('Se déconnecter') }}
                                </x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-gray-300 hover:text-white px-3 py-2 transition">
                        {{ __('Connexion') }}
                    </a>
                    <a href="{{ route('register') }}" wire:navigate class="ms-1 text-sm font-semibold bg-brand-800 hover:bg-brand-700 text-white px-4 py-2 rounded-full transition">
                        {{ __('Inscription') }}
                    </a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center gap-1 lg:hidden">
                <x-theme-toggle />

                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-white/10 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden border-t border-white/10">
        <form action="{{ route('products.index') }}" method="GET" class="px-4 pt-3">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M18 10.5a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                </svg>
                <input type="text" name="q" placeholder="{{ __('Rechercher...') }}"
                       class="w-full bg-white/10 border-transparent rounded-full text-sm text-white placeholder-gray-400 pl-9 pr-4 py-2 focus:bg-white focus:text-gray-900 focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
            </div>
        </form>

        <div class="pt-3 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')" wire:navigate>
                {{ __('Produits') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('sellers.index')" :active="request()->routeIs('sellers.*')" wire:navigate>
                {{ __('Vendeurs') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('wanted.index')" :active="request()->routeIs('wanted.*')" wire:navigate>
                {{ __('Recherches') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Tableau de bord') }}
                </x-responsive-nav-link>
                @if (auth()->user()->isVendeur() || auth()->user()->isAdmin())
                    <x-responsive-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')" wire:navigate>
                        {{ auth()->user()->isAdmin() ? __('Ma boutique') : __('Espace vendeur') }}
                    </x-responsive-nav-link>
                @endif
                @if (auth()->user()->isAdmin() && Route::has('admin.dashboard'))
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')" wire:navigate>
                        {{ __('Administration') }}
                    </x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')" wire:navigate>
                    {{ __('Messages') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('wishlist.index')" :active="request()->routeIs('wishlist.index')" wire:navigate>
                    {{ __('Favoris') }}@if ($wishlistCount) <span class="ms-1 text-brand-400">({{ $wishlistCount }})</span>@endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cart.show')" :active="request()->routeIs('cart.show')" wire:navigate>
                    {{ __('Panier') }}@if ($cartCount) <span class="ms-1 text-brand-400">({{ $cartCount }})</span>@endif
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-white/10">
            @auth
                <div class="px-4 flex items-center gap-3">
                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-white/10 text-white font-semibold text-sm shrink-0">
                        {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <div class="font-medium text-base text-white truncate" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                        <div class="text-sm text-gray-400 truncate">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profil') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Se déconnecter') }}
                        </x-responsive-nav-link>
                    </button>
                </div>
            @else
                <div class="px-4 space-y-2">
                    <x-responsive-nav-link :href="route('login')" wire:navigate>
                        {{ __('Connexion') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')" wire:navigate>
                        {{ __('Inscription') }}
                    </x-responsive-nav-link>
                </div>
            @endauth
        </div>
    </div>
</nav>
