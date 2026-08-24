<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'categorie')]
    public string $category = '';

    #[Url]
    public string $brand = '';

    #[Url]
    public string $condition = '';

    #[Url]
    public string $rarity = '';

    #[Url(as: 'min')]
    public ?float $minPrice = null;

    #[Url(as: 'max')]
    public ?float $maxPrice = null;

    #[Url]
    public string $sort = 'newest';

    #[Url(as: 'q')]
    public string $search = '';

    public function updating($property): void
    {
        if (in_array($property, ['category', 'brand', 'condition', 'rarity', 'minPrice', 'maxPrice', 'sort', 'search'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['category', 'brand', 'condition', 'rarity', 'minPrice', 'maxPrice', 'search']);
        $this->sort = 'newest';
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Product::active()->with(['images', 'seller', 'category']);

        if ($this->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $this->category));
        }

        if ($this->brand) {
            $query->where('brand', $this->brand);
        }

        if ($this->condition) {
            $query->where('condition', $this->condition);
        }

        if ($this->rarity) {
            $query->where('rarity', $this->rarity);
        }

        if ($this->minPrice !== null) {
            $query->where('price', '>=', $this->minPrice);
        }

        if ($this->maxPrice !== null) {
            $query->where('price', '<=', $this->maxPrice);
        }

        if ($this->search !== '') {
            $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%"));
        }

        match ($this->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popularity' => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            default => $query->latest(),
        };

        return [
            'products' => $query->paginate(12),
            'categories' => Category::orderBy('name')->get(),
            'brands' => Product::active()->whereNotNull('brand')->distinct()->orderBy('brand')->pluck('brand'),
            'rarities' => Product::active()->whereNotNull('rarity')->distinct()->orderBy('rarity')->pluck('rarity'),
        ];
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Catalogue') }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <aside class="lg:col-span-1 space-y-5 bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm h-fit lg:sticky lg:top-20">
            <div>
                <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Recherche') }}</label>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Titre ou description...') }}"
                       class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Catégorie') }}</label>
                <select wire:model.live="category" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">{{ __('Toutes les catégories') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Marque / série') }}</label>
                <select wire:model.live="brand" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">{{ __('Toutes') }}</option>
                    @foreach ($brands as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('État') }}</label>
                <select wire:model.live="condition" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="neuf">{{ __('Neuf') }}</option>
                    <option value="comme_neuf">{{ __('Comme neuf') }}</option>
                    <option value="bon_etat">{{ __('Bon état') }}</option>
                    <option value="usage">{{ __('Usagé') }}</option>
                </select>
            </div>

            @if ($rarities->isNotEmpty())
                <div>
                    <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Rareté') }}</label>
                    <select wire:model.live="rarity" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">{{ __('Toutes') }}</option>
                        @foreach ($rarities as $r)
                            <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Prix (€)') }}</label>
                <div class="flex items-center gap-2 mt-1.5">
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.400ms="minPrice" placeholder="{{ __('Min') }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <span class="text-gray-400">–</span>
                    <input type="number" min="0" step="0.01" wire:model.live.debounce.400ms="maxPrice" placeholder="{{ __('Max') }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>

            <button type="button" wire:click="resetFilters" class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                {{ __('Réinitialiser les filtres') }}
            </button>
        </aside>

        <div class="lg:col-span-3">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    {{ __(':count résultats', ['count' => $products->total()]) }}
                    <svg wire:loading wire:target="search,category,brand,condition,rarity,minPrice,maxPrice,sort" class="animate-spin h-3.5 w-3.5 text-brand-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </p>

                <select wire:model.live="sort" class="rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="newest">{{ __('Nouveautés') }}</option>
                    <option value="price_asc">{{ __('Prix croissant') }}</option>
                    <option value="price_desc">{{ __('Prix décroissant') }}</option>
                    <option value="popularity">{{ __('Popularité') }}</option>
                </select>
            </div>

            <div wire:loading.class="opacity-50" wire:target="search,category,brand,condition,rarity,minPrice,maxPrice,sort" class="transition-opacity">
                @if ($products->isEmpty())
                    <div class="py-16 text-center">
                        <div class="text-5xl mb-3">🔍</div>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('Aucun produit ne correspond à votre recherche.') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" wire:key="product-{{ $product->id }}" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
