<?php

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\WishlistItem;
use Livewire\Attributes\Locked;
use Livewire\Component;

class WishlistToggle extends Component
{
    #[Locked]
    public Product $product;

    #[Locked]
    public bool $compact = true;

    public bool $isWishlisted = false;

    public function mount(Product $product, bool $compact = true): void
    {
        $this->product = $product;
        $this->compact = $compact;
        $this->isWishlisted = auth()->check()
            && auth()->user()->wishlistedProducts()->where('product_id', $product->id)->exists();
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            return;
        }

        $existing = WishlistItem::where(['user_id' => auth()->id(), 'product_id' => $this->product->id])->first();

        if ($existing) {
            $existing->delete();
            $this->isWishlisted = false;
        } else {
            WishlistItem::create(['user_id' => auth()->id(), 'product_id' => $this->product->id]);
            $this->isWishlisted = true;
        }

        $this->dispatch('wishlist-updated');
    }

    public function render()
    {
        return view('livewire.components.wishlist-toggle');
    }
}
