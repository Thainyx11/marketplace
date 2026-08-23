<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Cart persistence per cahier des charges section 4.3: session for guests,
 * database for authenticated users. Guest quantities are clamped against
 * live stock on read since nothing enforces it while sitting in the session.
 */
class CartManager
{
    private const SESSION_KEY = 'guest_cart';

    public function __construct(private readonly ?User $user)
    {
    }

    public function items(): Collection
    {
        if ($this->user) {
            return $this->user->cart?->items()->with('product.images')->get()
                ->map(fn (CartItem $item) => ['product' => $item->product, 'quantity' => $item->quantity, 'key' => $item->product_id])
                ?? collect();
        }

        $products = Product::whereIn('id', array_keys($this->sessionCart()))->with('images')->get()->keyBy('id');

        return collect($this->sessionCart())
            ->map(fn (int $quantity, int $productId) => $products->has($productId)
                ? ['product' => $products[$productId], 'quantity' => min($quantity, $products[$productId]->stock), 'key' => $productId]
                : null)
            ->filter()
            ->values();
    }

    public function add(Product $product, int $quantity): void
    {
        $quantity = max(1, $quantity);

        if ($this->user) {
            $cart = Cart::firstOrCreate(['user_id' => $this->user->id]);
            $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
            $item->quantity = min($product->stock, ($item->exists ? $item->quantity : 0) + $quantity);
            $item->save();

            return;
        }

        $cart = $this->sessionCart();
        $cart[$product->id] = min($product->stock, ($cart[$product->id] ?? 0) + $quantity);
        $this->saveSessionCart($cart);
    }

    public function updateQuantity(Product $product, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($product);

            return;
        }

        $quantity = min($quantity, $product->stock);

        if ($this->user) {
            $this->user->cart?->items()->where('product_id', $product->id)->update(['quantity' => $quantity]);

            return;
        }

        $cart = $this->sessionCart();
        $cart[$product->id] = $quantity;
        $this->saveSessionCart($cart);
    }

    public function remove(Product $product): void
    {
        if ($this->user) {
            $this->user->cart?->items()->where('product_id', $product->id)->delete();

            return;
        }

        $cart = $this->sessionCart();
        unset($cart[$product->id]);
        $this->saveSessionCart($cart);
    }

    public function clear(): void
    {
        if ($this->user) {
            $this->user->cart?->items()->delete();

            return;
        }

        $this->saveSessionCart([]);
    }

    public function total(): float
    {
        return (float) $this->items()->sum(fn (array $entry) => $entry['quantity'] * $entry['product']->price);
    }

    public function count(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /** Called right after login so items added as a guest aren't lost. */
    public static function mergeSessionIntoDb(User $user): void
    {
        $session = session(self::SESSION_KEY, []);

        if (empty($session)) {
            return;
        }

        $manager = new self($user);

        foreach ($session as $productId => $quantity) {
            if ($product = Product::find($productId)) {
                $manager->add($product, $quantity);
            }
        }

        session()->forget(self::SESSION_KEY);
    }

    private function sessionCart(): array
    {
        return session(self::SESSION_KEY, []);
    }

    private function saveSessionCart(array $cart): void
    {
        session([self::SESSION_KEY => array_filter($cart)]);
    }
}
