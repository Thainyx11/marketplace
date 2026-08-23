<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public function with(): array
    {
        $query = User::where('role', 'vendeur')->where('is_approved', true)->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->active()]);

        if ($this->search !== '') {
            $query->where(fn ($q) => $q
                ->where('shop_name', 'like', "%{$this->search}%")
                ->orWhere('name', 'like', "%{$this->search}%"));
        }

        return ['sellers' => $query->orderBy('shop_name')->paginate(12)];
    }
}; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Vendeurs') }}</h1>

    <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Rechercher une boutique...') }}"
           class="w-full max-w-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm mb-6">

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($sellers as $seller)
            <a href="{{ route('sellers.show', $seller->shop_slug) }}" wire:navigate
               class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 hover:shadow-md transition">
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $seller->shop_name ?? $seller->name }}</p>
                @if ($seller->bio)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $seller->bio }}</p>
                @endif
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __(':count articles en vente', ['count' => $seller->products_count]) }}</p>
            </a>
        @endforeach
    </div>

    @if ($sellers->isEmpty())
        <p class="text-gray-500 dark:text-gray-400 py-12 text-center">{{ __('Aucun vendeur trouvé.') }}</p>
    @endif

    <div class="mt-8">{{ $sellers->links() }}</div>
</div>
