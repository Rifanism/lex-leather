<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function create(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Keranjangmu masih kosong.');
        }

        return view('checkout.create', [
            'rows' => $this->cart->rows(),
            'total' => $this->cart->total(),
            'setting' => PaymentSetting::current(),
            'user' => request()->user(),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Keranjangmu masih kosong.');
        }

        $data = $request->validated();

        $order = DB::transaction(function () use ($data, $request) {
            $quantities = $this->cart->all();

            // lockForUpdate holds a row lock for the rest of the transaction, so
            // two customers checking out at the same instant cannot both claim
            // the last unit of a product.
            $products = Product::query()
                ->whereIn('id', array_keys($quantities))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $items = [];
            $total = 0;

            foreach ($quantities as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu produk di keranjang sudah tidak tersedia. Silakan perbarui keranjangmu.',
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$product->name} tidak mencukupi. Tersisa {$product->stock}.",
                    ]);
                }

                // Snapshot taken here, from the live row -- never from the session.
                $subtotal = $product->price * $quantity;
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'shipping_address' => $data['shipping_address'],
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'note' => $data['note'] ?? null,
            ]);

            $order->items()->createMany($items);

            // Stock is reserved as soon as the order exists, not when it is paid.
            foreach ($quantities as $productId => $quantity) {
                $products->get($productId)->decrement('stock', $quantity);
            }

            return $order;
        });

        // Only cleared after the transaction commits, so a failed checkout
        // leaves the cart intact.
        $this->cart->clear();

        return redirect()->route('orders.show', $order)
            ->with('status', 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran.');
    }
}
