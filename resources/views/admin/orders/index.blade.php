<x-app-layout title="Pesanan Pelanggan">
    <x-slot name="header">
        <p class="eyebrow">Administrasi</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Pesanan Pelanggan</h1>
        <p class="mt-2 text-sm text-espresso-800/60">{{ $orders->total() }} pesanan tercatat.</p>
    </x-slot>

    <form method="GET" class="card mb-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[14rem] flex-1">
            <label for="q" class="label">Cari</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="No. order / nama / email" class="field mt-1.5">
        </div>

        <div class="min-w-[12rem]">
            <label for="status" class="label">Status</label>
            <x-select id="status" name="status" class="mt-1.5">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                        {{ \App\Models\Order::statusLabels()[$status] ?? $status }}
                    </option>
                @endforeach
            </x-select>
        </div>

        <x-primary-button><x-icon name="filter" /> Terapkan</x-primary-button>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Order</th>
                    <th scope="col">Pelanggan</th>
                    <th scope="col">Bayar</th>
                    <th scope="col" class="text-end">Total</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="break-anywhere font-semibold text-cognac-600 hover:underline">
                                {{ $order->order_number }}
                            </a>
                            <p class="mt-0.5 text-xs text-espresso-800/45">{{ $order->created_at->translatedFormat('d M Y H:i') }}</p>
                        </td>
                        <td>
                            <p class="font-medium text-espresso-900">{{ $order->customer_name }}</p>
                            <p class="break-anywhere mt-0.5 text-xs text-espresso-800/45">{{ $order->customer_email }}</p>
                        </td>
                        <td class="text-sm text-espresso-800/60">{{ $order->paymentMethodLabel() }}</td>
                        <td class="text-end font-medium tabular-nums text-espresso-900">{{ $order->formattedTotal() }}</td>
                        <td><x-order-status :status="$order->status" /></td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-ghost btn-sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center">
                            <p class="font-display text-base font-semibold text-espresso-900">Belum ada pesanan</p>
                            <p class="mt-1 text-sm text-espresso-800/55">Pesanan pelanggan akan muncul di sini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">{{ $orders->links() }}</div>
</x-app-layout>