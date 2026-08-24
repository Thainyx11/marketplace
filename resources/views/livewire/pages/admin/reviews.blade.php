<?php

use App\Models\Review;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    /** Defense-in-depth: the route group already requires role:admin. */
    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    public function delete(Review $review): void
    {
        $review->delete();
    }

    public function with(): array
    {
        return ['reviews' => Review::with(['product', 'order.buyer'])->latest()->paginate(15)];
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($reviews as $review)
            <div class="p-4" wire:key="review-{{ $review->id }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $review->product->title }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Par') }} {{ $review->order->buyer->name }} · <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span></p>
                    </div>
                    <button type="button" wire:click="delete({{ $review->id }})" wire:confirm="{{ __('Supprimer cet avis ?') }}"
                            class="text-sm text-red-500 hover:underline">
                        {{ __('Supprimer') }}
                    </button>
                </div>
                @if ($review->comment)
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">{{ $review->comment }}</p>
                @endif
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 p-8 text-center">{{ __('Aucun avis pour le moment.') }}</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $reviews->links() }}</div>
</div>
