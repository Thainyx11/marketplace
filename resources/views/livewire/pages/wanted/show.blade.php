<?php

use App\Models\Product;
use App\Models\WantedItem;
use App\Models\WantedItemResponse;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public WantedItem $wantedItem;

    public string $content = '';

    public ?int $product_id = null;

    public function mount(WantedItem $wantedItem): void
    {
        $this->wantedItem = $wantedItem->load(['user', 'category', 'responses.seller', 'responses.product.images']);
    }

    public function respond(): void
    {
        Gate::authorize('respond', $this->wantedItem);

        $validated = $this->validate([
            'content' => ['required', 'string', 'max:2000'],
            'product_id' => ['nullable', 'exists:products,id'],
        ]);

        WantedItemResponse::create([
            ...$validated,
            'wanted_item_id' => $this->wantedItem->id,
            'seller_id' => auth()->id(),
        ]);

        $this->content = '';
        $this->product_id = null;
        $this->wantedItem->refresh()->load(['responses.seller', 'responses.product.images']);
    }

    public function markFulfilled(): void
    {
        Gate::authorize('update', $this->wantedItem);

        $this->wantedItem->update(['status' => 'pourvue']);
    }

    public function close(): void
    {
        Gate::authorize('update', $this->wantedItem);

        $this->wantedItem->update(['status' => 'fermee']);
    }

    public function with(): array
    {
        return [
            'myProducts' => auth()->check() && Gate::allows('respond', $this->wantedItem)
                ? auth()->user()->products()->active()->get()
                : collect(),
        ];
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('wanted.index') }}" wire:navigate class="text-sm text-gray-500 dark:text-gray-400 hover:text-brand-600 dark:hover:text-brand-400">
        ← {{ __('Toutes les recherches') }}
    </a>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mt-4">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-extrabold text-gray-900 dark:text-gray-100">{{ $wantedItem->title }}</h1>
                    @if ($wantedItem->category)
                        <x-badge color="gray">{{ $wantedItem->category->name }}</x-badge>
                    @endif
                    <x-badge :color="match ($wantedItem->status) { 'ouverte' => 'emerald', 'pourvue' => 'brand', 'fermee' => 'gray' }">
                        {{ ['ouverte' => 'Ouverte', 'pourvue' => 'Pourvue', 'fermee' => 'Clôturée'][$wantedItem->status] }}
                    </x-badge>
                </div>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                    {{ __('Par') }} {{ $wantedItem->user->name }} · {{ $wantedItem->created_at->diffForHumans() }}
                    @if ($wantedItem->max_price)
                        · {{ __('Budget max :price €', ['price' => number_format($wantedItem->max_price, 2, ',', ' ')]) }}
                    @endif
                </p>
            </div>

            @if (auth()->id() === $wantedItem->user_id && $wantedItem->status === 'ouverte')
                <div class="flex gap-2 shrink-0">
                    <x-secondary-button wire:click="markFulfilled" wire:confirm="{{ __('Marquer cette recherche comme pourvue ?') }}">
                        {{ __('Marquer comme pourvue') }}
                    </x-secondary-button>
                    <x-secondary-button wire:click="close" wire:confirm="{{ __('Clôturer cette recherche ?') }}">
                        {{ __('Clôturer') }}
                    </x-secondary-button>
                </div>
            @endif
        </div>

        @if ($wantedItem->description)
            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line mt-4 border-t border-gray-100 dark:border-gray-700 pt-4">{{ $wantedItem->description }}</p>
        @endif
    </div>

    @auth
        @if (Gate::allows('respond', $wantedItem))
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 mt-4">
                <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __('Répondre à cette recherche') }}</h2>
                <form wire:submit="respond" class="space-y-3">
                    <textarea wire:model="content" rows="3" placeholder="{{ __("J'ai cet article, voici son état...") }}"
                              class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    @error('content') <p class="text-sm text-red-500">{{ $message }}</p> @enderror

                    @if ($myProducts->isNotEmpty())
                        <select wire:model="product_id" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">{{ __('Lier une de mes annonces (optionnel)') }}</option>
                            @foreach ($myProducts as $myProduct)
                                <option value="{{ $myProduct->id }}">{{ $myProduct->title }} — {{ number_format($myProduct->price, 2, ',', ' ') }} €</option>
                            @endforeach
                        </select>
                    @endif

                    <x-primary-button type="submit">{{ __('Envoyer ma réponse') }}</x-primary-button>
                </form>
            </div>
        @endif
    @endauth

    <div class="mt-6">
        <h2 class="font-bold text-gray-900 dark:text-gray-100 mb-3">{{ __(':count réponses', ['count' => $wantedItem->responses->count()]) }}</h2>

        @if ($wantedItem->responses->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Aucune réponse pour le moment.') }}</p>
        @else
            <div class="space-y-3">
                @foreach ($wantedItem->responses as $response)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4" wire:key="response-{{ $response->id }}">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $response->seller->shop_name ?? $response->seller->name }}</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-line">{{ $response->content }}</p>

                        @if ($response->product)
                            <a href="{{ route('products.show', $response->product->slug) }}" wire:navigate
                               class="mt-3 flex items-center gap-3 bg-gray-50 dark:bg-gray-900 rounded-xl p-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <div class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden shrink-0">
                                    @if ($response->product->images->first())
                                        <img src="{{ $response->product->images->first()->url }}" class="object-cover w-full h-full">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $response->product->title }}</p>
                                    <p class="text-sm font-bold text-brand-600 dark:text-brand-400">{{ number_format($response->product->price, 2, ',', ' ') }} €</p>
                                </div>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
