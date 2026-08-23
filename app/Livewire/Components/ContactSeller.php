<?php

namespace App\Livewire\Components;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ContactSeller extends Component
{
    #[Locked]
    public Product $product;

    public string $content = '';

    public bool $sent = false;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function send(): void
    {
        Gate::authorize('create', Message::class);

        $this->validate(['content' => ['required', 'string', 'max:2000']]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->product->user_id,
            'product_id' => $this->product->id,
            'content' => $this->content,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $this->content = '';
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.components.contact-seller');
    }
}
