<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $orders = Order::query()
            ->with('items')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_email', 'like', "%{$term}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items', 'user'),
            'statuses' => Order::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        DB::transaction(function () use ($order, $data) {
            $previous = $order->status;

            $order->update($data);

            // Restock only on the transition *into* cancelled, so re-saving the
            // same status cannot hand out the stock twice.
            if ($data['status'] === 'cancelled' && $previous !== 'cancelled') {
                $this->restoreStock($order);
            }
        });

        return back()->with('status', "Status pesanan diubah menjadi \"{$order->fresh()->statusLabel()}\".");
    }

    /**
     * Give back the reserved units for every line on the order.
     */
    private function restoreStock(Order $order): void
    {
        foreach ($order->items()->lockForUpdate()->get() as $item) {
            $item->product()->increment('stock', $item->quantity);
        }
    }
}
