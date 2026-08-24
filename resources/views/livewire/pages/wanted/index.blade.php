<?php

use App\Models\WantedItem;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public function with(): array
    {
        return [
            'items' => WantedItem::open()
                ->with(['user', 'category'])
                ->withCount('responses')
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100">{{ __('Recherches') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __("Des acheteurs recherchent des articles précis — répondez si vous en avez un à vendre.") }}</p>
        </div>
        <a href="{{ route('wanted.create') }}" wire:navigate class="shrink-0 bg-brand-800 hover:bg-brand-700 text-white font-semibold px-4 py-2.5 rounded-full text-sm transition">
            {{ __('Publier une recherche') }}
        </a>
    </div>

    @if ($items->isEmpty())
        <div class="py-16 text-center">
            <div class="text-5xl mb-3">🔎</div>
            <p class="text-gray-500 dark:text-gray-400">{{ __('Aucune recherche ouverte pour le moment.') }}</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($items as $item)
                <a href="{{ route('wanted.show', $item) }}" wire:navigate wire:key="wanted-{{ $item->id }}"
                   class="block bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $item->title }}</p>
                                @if ($item->category)
                                    <x-badge color="gray">{{ $item->category->name }}</x-badge>
                                @endif
                            </div>
                            @if ($item->description)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $item->description }}</p>
                            @endif
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                {{ __('Par') }} {{ $item->user->name }} · {{ $item->created_at->diffForHumans() }}
                                @if ($item->max_price)
                                    · {{ __('Budget max :price €', ['price' => number_format($item->max_price, 2, ',', ' ')]) }}
                                @endif
                            </p>
                        </div>
                        <div class="shrink-0 text-sm font-semibold text-brand-600 dark:text-brand-400 whitespace-nowrap">
                            {{ __(':count réponses', ['count' => $item->responses_count]) }}
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $items->links() }}
        </div>
    @endif
</div>
