<?php

namespace App\Livewire\Components;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class LeaveReview extends Component
{
    #[Locked]
    public Order $order;

    #[Locked]
    public OrderItem $orderItem;

    public int $rating = 5;

    public string $comment = '';

    public ?Review $existing = null;

    public function mount(Order $order, OrderItem $orderItem): void
    {
        $this->order = $order;
        $this->orderItem = $orderItem;
        $this->existing = $orderItem->existingReview();
    }

    public function submit(): void
    {
        Gate::authorize('create', [Review::class, $this->order, $this->orderItem]);

        $this->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->existing = Review::create([
            'order_id' => $this->order->id,
            'product_id' => $this->orderItem->product_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);
    }

    public function render()
    {
        return view('livewire.components.leave-review');
    }
}
