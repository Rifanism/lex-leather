<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('orders.index', [
            'orders' => $request->user()
                ->orders()
                ->with('items')
                ->latest()
                ->get(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOwner($request, $order);

        return view('orders.show', [
            'order' => $order->load('items'),
            'setting' => PaymentSetting::current(),
        ]);
    }

    /**
     * Dummy payment: flips pending -> paid with no external gateway.
     * Idempotent on purpose, so a double-click cannot raise an error.
     */
    public function pay(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($request, $order);

        if ($order->canBePaidByCustomer()) {
            $order->update(['status' => 'paid']);

            return back()->with('status', 'Pembayaran berhasil dikonfirmasi.');
        }

        return back()->with('status', $order->isPrepaid()
            ? 'Pesanan ini sudah diproses.'
            : 'Pesanan COD dibayar saat barang diterima.');
    }

    private function authorizeOwner(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 403);
    }
}
