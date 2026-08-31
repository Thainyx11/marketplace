<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public User $seller;

    public function mount(string $shop_slug): void
    {
        $seller = User::whereIn('role', ['vendeur', 'admin'])->where('shop_slug', $shop_slug)->first();

        abort_if(! $seller, 404);

        $this->seller = $seller;
    }

    // FIX: same SEO gap as the product page (§ products/show.blade.php) — a
    // shop page ranking under its own shop name needs its own <title>.
    public function render(): mixed
    {
        return parent::render()
            ->title("{$this->seller->shop_name} — ".config('app.name'))
            ->layoutData(['metaDescription' => "Découvrez la boutique {$this->seller->shop_name} sur ".config('app.name').' : cartes à collectionner, jeux vidéo rétro, figurines, manga et goodies vendus par un particulier passionné.']);
    }

    public function with(): array
    {
        return [
            'products' => $this->seller->products()->active()->with(['images', 'category'])->latest()->paginate(12),
            'averageRating' => $this->seller->averageRating(),
            'reviewsCount' => \App\Models\Review::whereHas('product', fn ($q) => $q->where('user_id', $this->seller->id))->count(),
            // Trust signals for a C2C marketplace: a star rating alone doesn't
            // say much with 0-1 reviews, sales count and tenure round it out.
            'salesCount' => $this->seller->saleItems()->where('status', 'livree')->count(),
        ];
    }
}; ?>

<div>
    <div class="h-28 sm:h-36 bg-gradient-to-br from-brand-800 via-brand-900 to-brand-950"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 -mt-14 relative mb-8">
            <div class="flex items-end gap-4">
                <span class="grid place-items-center h-20 w-20 rounded-2xl bg-gradient-to-br from-brand-700 to-brand-900 text-white font-extrabold text-3xl shrink-0 ring-4 ring-white dark:ring-gray-800 -mt-2">
                    {{ Str::upper(Str::substr($seller->shop_name ?? $seller->name, 0, 1)) }}
                </span>
                <div class="min-w-0 pb-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-gray-100 truncate">{{ $seller->shop_name ?? $seller->name }}</h1>
                        @if ($seller->isAdmin())
                            <x-badge color="brand" title="{{ __('Boutique tenue par la plateforme') }}">
                                <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1.5l2.6 5.6 6.1.6-4.6 4.2 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4.2 6.1-.6L12 1.5z"/></svg>
                                {{ __('Vendeur officiel') }}
                            </x-badge>
                        @endif
                    </div>
                    @if ($averageRating)
                        <p class="text-amber-500 text-sm font-medium">★ {{ number_format($averageRating, 1) }} <span class="text-gray-500 dark:text-gray-400">({{ __(':count avis', ['count' => $reviewsCount]) }})</span></p>
                    @endif
                </div>
            </div>

            @if ($seller->bio)
                <p class="text-gray-700 dark:text-gray-300 mt-4">{{ $seller->bio }}</p>
            @endif

            <div class="flex items-center gap-5 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                    {{ trans_choice(':count vente réalisée|:count ventes réalisées', $salesCount, ['count' => $salesCount]) }}
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                    {{ __('Membre depuis :date', ['date' => $seller->created_at->translatedFormat('F Y')]) }}
                </span>
            </div>
        </div>

        <div class="pb-16">
            <h2 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-4">{{ __('Produits en vente') }}</h2>

            @if ($products->isEmpty())
                <div class="py-12 text-center">
                    <svg class="h-12 w-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">{{ __("Cette boutique n'a aucun produit en vente actuellement.") }}</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" wire:key="seller-product-{{ $product->id }}" />
                    @endforeach
                </div>

                <div class="mt-8">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>
