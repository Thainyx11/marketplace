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
    <div class="mb-4">
        <a href="{{ route('messages.index') }}" wire:navigate class="text-sm text-gray-500 dark:text-gray-400 hover:underline">← {{ __('Messages') }}</a>
        <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
            {{ $otherUser->shop_name ?? $otherUser->name }}
            <span class="text-gray-400 font-normal">— {{ $product->title }}</span>
        </h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 messages-scroll h-96 overflow-y-auto space-y-3" wire:poll.5s="receiveMessage">
        @foreach ($messages as $message)
            <div class="flex {{ $message['sender_id'] === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs">
                    <div @class([
                        'rounded-lg px-3 py-2 text-sm',
                        'bg-indigo-600 text-white' => $message['sender_id'] === auth()->id(),
                        'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' => $message['sender_id'] !== auth()->id(),
                    ])>
                        {{ $message['content'] }}
                    </div>
                    @if ($message['sender_id'] !== auth()->id())
                        <button type="button" wire:click="report({{ $message['id'] }})" class="text-xs text-gray-400 hover:underline mt-0.5">
                            {{ __('Signaler') }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <form wire:submit="send" class="flex items-center gap-2 mt-4">
        <input type="text" wire:model="content" placeholder="{{ __('Votre message...') }}"
               class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-2 rounded-lg text-sm">
            {{ __('Envoyer') }}
        </button>
    </form>
    @error('content') <p class="text-sm text-red-500 mt-1">{{ $message }}</p> @enderror
</div>
