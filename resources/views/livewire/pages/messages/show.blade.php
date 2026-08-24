<?php

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Product $product;

    public User $otherUser;

    public string $content = '';

    public array $messages = [];

    public function mount(Product $product, User $user): void
    {
        Gate::authorize('create', Message::class);

        abort_if($user->id === auth()->id(), 403);
        abort_unless($product->user_id === $user->id || $product->user_id === auth()->id(), 403);

        $this->product = $product;
        $this->otherUser = $user;

        $this->loadMessages();

        Message::where('sender_id', $user->id)
            ->where('receiver_id', auth()->id())
            ->where('product_id', $product->id)
            ->update(['seen' => true]);
    }

    public function send(): void
    {
        $this->validate(['content' => ['required', 'string', 'max:2000']]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->otherUser->id,
            'product_id' => $this->product->id,
            'content' => $this->content,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $this->content = '';
        $this->loadMessages();
    }

    public function report(int $messageId): void
    {
        $message = Message::findOrFail($messageId);
        Gate::authorize('report', $message);

        \App\Models\MessageReport::firstOrCreate([
            'message_id' => $message->id,
            'reported_by' => auth()->id(),
        ]);

        session()->flash('status', __('Message signalé aux administrateurs.'));
    }

    public function receiveMessage(): void
    {
        $this->loadMessages();

        Message::where('sender_id', $this->otherUser->id)
            ->where('receiver_id', auth()->id())
            ->where('product_id', $this->product->id)
            ->update(['seen' => true]);
    }

    private function loadMessages(): void
    {
        $this->messages = Message::where('product_id', $this->product->id)
            ->where(function ($q) {
                $q->where(fn ($q2) => $q2->where('sender_id', auth()->id())->where('receiver_id', $this->otherUser->id))
                    ->orWhere(fn ($q2) => $q2->where('sender_id', $this->otherUser->id)->where('receiver_id', auth()->id()));
            })
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function channelName(): string
    {
        return Message::threadChannel($this->product->id, auth()->id(), $this->otherUser->id);
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data
     x-init="
        Echo.private('{{ $this->channelName() }}').listen('.message.sent', (e) => { $wire.receiveMessage(); });
        $watch('$el.scrollHeight', () => { $el.querySelector('.messages-scroll')?.scrollTo(0, 999999); });
     ">
    <div class="mb-4 flex items-center gap-3">
        <a href="{{ route('messages.index') }}" wire:navigate class="text-gray-400 hover:text-violet-600 dark:hover:text-violet-400 transition">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <span class="grid place-items-center h-10 w-10 rounded-full bg-gradient-to-br from-violet-500 to-fuchsia-600 text-white font-bold shrink-0">
            {{ Str::upper(Str::substr($otherUser->shop_name ?? $otherUser->name, 0, 1)) }}
        </span>
        <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">
            {{ $otherUser->shop_name ?? $otherUser->name }}
            <span class="text-gray-400 font-normal text-sm block sm:inline">— {{ $product->title }}</span>
        </h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 messages-scroll h-96 overflow-y-auto space-y-3" wire:poll.5s="receiveMessage">
        @foreach ($messages as $message)
            <div class="flex {{ $message['sender_id'] === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs">
                    <div @class([
                        'rounded-2xl px-3.5 py-2 text-sm',
                        'bg-violet-600 text-white rounded-br-sm' => $message['sender_id'] === auth()->id(),
                        'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-bl-sm' => $message['sender_id'] !== auth()->id(),
                    ])>
                        {{ $message['content'] }}
                    </div>
                    @if ($message['sender_id'] !== auth()->id())
                        <button type="button" wire:click="report({{ $message['id'] }})" class="text-xs text-gray-400 hover:text-red-500 hover:underline mt-1 ms-1">
                            {{ __('Signaler') }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <form wire:submit="send" class="flex items-center gap-2 mt-4">
        <input type="text" wire:model="content" placeholder="{{ __('Votre message...') }}"
               class="flex-1 rounded-full border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm focus:border-violet-500 focus:ring-violet-500">
        <button type="submit" wire:loading.attr="disabled" wire:target="send"
                class="bg-violet-600 hover:bg-violet-500 disabled:opacity-60 text-white font-semibold px-5 py-2.5 rounded-full text-sm transition">
            <span wire:loading.remove wire:target="send">{{ __('Envoyer') }}</span>
            <span wire:loading wire:target="send">{{ __('...') }}</span>
        </button>
    </form>
    @error('content') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
</div>
