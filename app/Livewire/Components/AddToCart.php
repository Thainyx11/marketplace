<?php

namespace App\Livewire\Components;

use App\Models\Product;
use App\Services\CartManager;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AddToCart extends Component
{
    #[Locked]
    public Product $product;

    public int $quantity = 1;

    public ?string $status = null;

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function add(): void
    {
        if (auth()->check() && ! auth()->user()->isAcheteur()) {
            $this->status = __('Seuls les comptes acheteur peuvent ajouter des articles au panier.');

            return;
        }

        $quantity = max(1, min($this->quantity, $this->product->stock));

        if ($quantity < 1) {
            $this->status = __('Cet article est épuisé.');

            return;
        }

        (new CartManager(auth()->user()))->add($this->product, $quantity);

        $this->status = __('Article ajouté au panier.');
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.components.add-to-cart');
    }
}
