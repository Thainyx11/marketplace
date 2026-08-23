<?php

use App\Models\Message;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function with(): array
    {
        $myId = auth()->id();

        $messages = Message::where('sender_id', $myId)->orWhere('receiver_id', $myId)
            ->with(['sender', 'receiver', 'product'])
            ->latest()
            ->get();

        $threads = $messages
            ->groupBy(fn (Message $m) => $m->product_id.'-'.($m->sender_id === $myId ? $m->receiver_id : $m->sender_id))
            ->map(function ($group) use ($myId) {
                $last = $group->first();
                $other = $last->sender_id === $myId ? $last->receiver : $last->sender;

                return [
                    'product' => $last->product,
                    'other' => $other,
                    'last_message' => $last,
                    'unread' => $group->where('receiver_id', $myId)->where('seen', false)->count(),
                ];
            })
            ->sortByDesc(fn ($t) => $t['last_message']->created_at)
            ->values();

        return ['threads' => $threads];
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Messages') }}</h1>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($threads as $thread)
            <a href="{{ route('messages.show', [$thread['product'], $thread['other']]) }}" wire:navigate
               class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-gray-100">
                        {{ $thread['other']->shop_name ?? $thread['other']->name }}
                        <span class="text-gray-400 dark:text-gray-500 font-normal">— {{ $thread['product']->title }}</span>
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $thread['last_message']->content }}</p>
                </div>

                <div class="text-right shrink-0">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $thread['last_message']->created_at->diffForHumans() }}</p>
                    @if ($thread['unread'] > 0)
                        <span class="inline-flex items-center justify-center bg-indigo-600 text-white text-xs rounded-full w-5 h-5 mt-1">{{ $thread['unread'] }}</span>
                    @endif
                </div>
            </a>
        @empty
            <p class="text-gray-500 dark:text-gray-400 p-8 text-center">{{ __('Aucune conversation pour le moment.') }}</p>
        @endforelse
    </div>
</div>
