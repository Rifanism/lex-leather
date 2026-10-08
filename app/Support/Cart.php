<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Session-backed cart. Stores only [product_id => quantity]; every price, name
 * and stock value is read live from the database so a cart can never go stale.
 */
class Cart
{
    /** @var array<int, int> */
    protected array $items;

    public function __construct()
    {
        $this->items = session('cart', []);
    }

    /** @return array<int, int> */
    public function all(): array
    {
        return $this->items;
    }

    public function quantity(int $productId): int
    {
        return $this->items[$productId] ?? 0;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /** Total number of units, for the navbar badge. */
    public function count(): int
    {
        return array_sum($this->items);
    }

    public function put(int $productId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->forget($productId);

            return;
        }

        $this->items[$productId] = $quantity;

        $this->flush();
    }

    public function increment(int $productId, int $quantity = 1): void
    {
        $this->put($productId, $this->quantity($productId) + $quantity);
    }

    public function forget(int $productId): void
    {
        unset($this->items[$productId]);

        $this->flush();
    }

    public function clear(): void
    {
        $this->items = [];

        session()->forget('cart');
    }

    /**
     * Live cart rows. Inactive products silently drop out of the cart.
     *
     * @return Collection<int, array{product: Product, quantity: int, subtotal: int}>
     */
    public function rows(): Collection
    {
        if ($this->isEmpty()) {
            return collect();
        }

        $quantities = $this->items;

        return Product::query()
            ->with('category')
            ->active()
            ->whereIn('id', array_keys($quantities))
            ->get()
            ->map(fn (Product $product) => [
                'product' => $product,
                'quantity' => $quantities[$product->id],
                'subtotal' => $product->price * $quantities[$product->id],
            ]);
    }

    public function total(): int
    {
        return (int) $this->rows()->sum('subtotal');
    }

    protected function flush(): void
    {
        session(['cart' => $this->items]);
    }
}
