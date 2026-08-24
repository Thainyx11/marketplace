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
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Vendeurs') }}</h1>

    <div class="relative max-w-sm mb-6">
        <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M18 10.5a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
        </svg>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Rechercher une boutique...') }}"
               class="w-full rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm pl-10 focus:border-violet-500 focus:ring-violet-500">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($sellers as $seller)
            <a href="{{ route('sellers.show', $seller->shop_slug) }}" wire:navigate
               class="flex items-start gap-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                <span class="grid place-items-center h-12 w-12 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white font-bold text-lg shrink-0">
                    {{ Str::upper(Str::substr($seller->shop_name ?? $seller->name, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $seller->shop_name ?? $seller->name }}</p>
                    @if ($seller->bio)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ $seller->bio }}</p>
                    @endif
                    <x-badge color="violet" class="mt-2">{{ __(':count articles', ['count' => $seller->products_count]) }}</x-badge>
                </div>
            </a>
        @endforeach
    </div>

    @if ($sellers->isEmpty())
        <div class="py-16 text-center">
            <div class="text-5xl mb-3">🏪</div>
            <p class="text-gray-500 dark:text-gray-400">{{ __('Aucun vendeur trouvé.') }}</p>
        </div>
    @endif

    <div class="mt-8">{{ $sellers->links() }}</div>
</div>
