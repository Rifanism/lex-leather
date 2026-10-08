<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function index(): View
    {
        return view('cart.index', [
            'rows' => $this->cart->rows(),
            'total' => $this->cart->total(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $product = Product::query()->active()->findOrFail($data['product_id']);

        $this->guardStock($product, $this->cart->quantity($product->id) + $data['quantity']);

        $this->cart->increment($product->id, $data['quantity']);

        return back()->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        if ($this->cart->quantity($product->id) === 0) {
            throw ValidationException::withMessages(['quantity' => 'Produk tidak ada di keranjang.']);
        }

        $this->guardStock($product, $data['quantity']);

        $this->cart->put($product->id, $data['quantity']);

        return back()->with('status', 'Keranjang diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->forget($product->id);

        return back()->with('status', 'Produk dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return back()->with('status', 'Keranjang dikosongkan.');
    }

    /**
     * Never let a customer put more units in the cart than exist.
     */
    private function guardStock(Product $product, int $wanted): void
    {
        if (! $product->is_active) {
            throw ValidationException::withMessages(['quantity' => 'Produk ini tidak tersedia.']);
        }

        if ($wanted > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Stok {$product->name} hanya tersisa {$product->stock}.",
            ]);
        }
    }
}
