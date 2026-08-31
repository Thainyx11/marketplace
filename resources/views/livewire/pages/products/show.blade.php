<?php

use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        Gate::authorize('view', $product);

        $this->product = $product->load(['images', 'seller', 'category', 'reviews' => fn ($q) => $q->latest()]);
    }

    // FIX: product pages all shared the same site-wide <title> — the single most
    // SEO-valuable page on a marketplace (one per listing) had no way to rank on
    // its own product name. Volt's class-based syntax has no title() convention
    // (that only exists for the functional API, checked in vendor/livewire/volt's
    // Component::render() — it's null for anything that isn't a FunctionalComponent);
    // the officially supported way for a class component is to chain the same
    // View::title()/layoutData() macros Volt itself uses, via a render() override.
    public function render(): mixed
    {
        return parent::render()
            ->title("{$this->product->title} — ".config('app.name'))
            ->layoutData(['metaDescription' => Str::limit(strip_tags($this->product->description), 155)]);
    }
}; ?>

@push('meta')
    <meta property="og:type" content="product">
    @if ($product->images->isNotEmpty())
        <meta property="og:image" content="{{ $product->images->first()->url }}">
    @endif
    <meta property="product:price:amount" content="{{ $product->price }}">
    <meta property="product:price:currency" content="EUR">
@endpush

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <nav class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        <a href="{{ route('products.index') }}" wire:navigate class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('Catalogue') }}</a>
        <span class="mx-1">/</span>
        <a href="{{ route('products.index', ['categorie' => $product->category->slug]) }}" wire:navigate class="hover:text-brand-600 dark:hover:text-brand-400">{{ $product->category->name }}</a>
    </nav>

    @php $isCard = $product->category->slug === 'cartes-a-collectionner'; @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div x-data="{ active: 0, zoomed: false, images: {{ $product->images->map(fn ($i) => $i->url)->toJson() }} }"
             @keydown.escape.window="zoomed = false">
            <div class="relative {{ $isCard ? 'aspect-[5/7]' : 'aspect-square' }} bg-gray-100 dark:bg-gray-800 rounded-2xl overflow-hidden flex items-center justify-center">
                <template x-if="images.length">
                    <button type="button" @click="zoomed = true" class="w-full h-full cursor-zoom-in group/zoom" aria-label="{{ __('Agrandir la photo') }}">
                        <img :src="images[active]" alt="{{ $product->title }}" class="object-cover w-full h-full">
                        <span class="absolute bottom-3 right-3 bg-black/60 text-white rounded-full p-2 opacity-0 group-hover/zoom:opacity-100 transition-opacity">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M18 10.5a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0zM10.5 7.5v6m-3-3h6" />
                            </svg>
                        </span>
                    </button>
                </template>
                @if ($product->images->isEmpty())
                    <div class="flex flex-col items-center gap-2 text-gray-300 dark:text-gray-600">
                        <svg class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 8.25V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V8.25M3 8.25V6a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 6v2.25m-18 0h18M8.25 6h.008v.008H8.25V6z" />
                        </svg>
                        <span class="text-xs font-medium uppercase tracking-wide">{{ __('Photo à venir') }}</span>
                    </div>
                @endif

                @if ($product->rarity)
                    <x-badge color="amber" class="absolute top-3 left-3">{{ ucfirst($product->rarity) }}</x-badge>
                @endif
            </div>

            @if ($product->images->count() > 1)
                <div class="grid grid-cols-5 gap-2 mt-2">
                    @foreach ($product->images as $index => $image)
                        <button type="button" @click="active = {{ $index }}"
                                :class="active === {{ $index }} ? 'ring-2 ring-brand-500' : 'ring-1 ring-transparent opacity-80 hover:opacity-100'"
                                class="{{ $isCard ? 'aspect-[5/7]' : 'aspect-square' }} bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden transition">
                            <img src="{{ $image->url }}" class="object-cover w-full h-full">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Zoom lightbox: for a collectible, seeing a card's edges or a figure's
                 paint detail up close is a trust feature, not just a nicety. --}}
            {{-- Plain x-show, deliberately no x-cloak or x-transition here: both were
                 tested and, combined with x-show on this element, left the lightbox
                 permanently stuck at display:none after opening (a known Alpine
                 x-cloak/x-show interaction) — verified by toggling the reactive state
                 directly and reading computed style before settling on this. --}}
            <div x-show="zoomed"
                 class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 sm:p-10"
                 @click="zoomed = false" role="dialog" aria-modal="true">
                <button type="button" @click.stop="zoomed = false"
                        class="absolute top-4 right-4 sm:top-6 sm:right-6 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-2.5 transition"
                        aria-label="{{ __('Fermer') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img :src="images[active]" @click.stop alt="{{ $product->title }}" class="max-h-full max-w-full object-contain rounded-lg">
            </div>
        </div>

        <div>
            @if ($product->status !== 'active')
                <div class="mb-4 bg-amber-50 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 rounded-lg p-3 text-sm">
                    {{ __('Cette annonce est actuellement :status.', ['status' => $product->status === 'hidden' ? 'masquée' : 'supprimée']) }}
                </div>
            @endif

            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-gray-100">{{ $product->title }}</h1>

            <div class="flex flex-wrap items-center gap-2 mt-3">
                @if ($product->brand)
                    <x-badge color="gray">{{ $product->brand }}</x-badge>
                @endif
                <x-badge color="brand">{{ ['neuf' => 'Neuf', 'comme_neuf' => 'Comme neuf', 'bon_etat' => 'Bon état', 'usage' => 'Usagé'][$product->condition] }}</x-badge>
            </div>

            <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5">
                <p class="text-3xl font-extrabold text-brand-600 dark:text-brand-400">{{ number_format($product->price, 2, ',', ' ') }} €</p>

                <p class="text-sm mt-1 font-medium {{ $product->stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                    {{ $product->stock > 0 ? __(':count en stock', ['count' => $product->stock]) : __('Épuisé') }}
                </p>

                <livewire:components.add-to-cart :product="$product" />
                <livewire:components.wishlist-toggle :product="$product" :compact="false" />
            </div>

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-2">{{ __('Description') }}</h2>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $product->description }}</p>
            </div>

            <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Vendeur') }}</h2>
                <a href="{{ route('sellers.show', $product->seller->shop_slug ?? $product->seller->id) }}" wire:navigate
                   class="flex items-center gap-3 bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm hover:shadow-md transition">
                    <span class="grid place-items-center h-11 w-11 rounded-full bg-gradient-to-br from-brand-700 to-brand-900 text-white font-bold shrink-0">
                        {{ Str::upper(Str::substr($product->seller->shop_name ?? $product->seller->name, 0, 1)) }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $product->seller->shop_name ?? $product->seller->name }}</p>
                        @if ($product->seller->averageRating())
                            <p class="text-sm text-amber-500">★ {{ number_format($product->seller->averageRating(), 1) }}</p>
                        @endif
                    </div>
                    <span class="text-sm font-semibold text-brand-600 dark:text-brand-400 shrink-0">{{ __('Voir la boutique →') }}</span>
                </a>

                @auth
                    @if (auth()->id() !== $product->user_id)
                        <livewire:components.contact-seller :product="$product" />
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                        <a href="{{ route('login') }}" wire:navigate class="underline">{{ __('Connectez-vous') }}</a>
                        {{ __('pour contacter le vendeur.') }}
                    </p>
                @endauth
            </div>

            @if ($product->reviews->isNotEmpty())
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Avis') }}</h2>
                    <div class="space-y-3">
                        @foreach ($product->reviews as $review)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm">
                                <p class="text-amber-500 text-sm">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                                @if ($review->comment)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
