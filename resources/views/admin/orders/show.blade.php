<x-app-layout :title="'Pesanan '.$order->order_number">
    <x-slot name="header">
        <nav class="mb-3 flex items-center gap-2 text-sm text-espresso-800/55" aria-label="Breadcrumb">
            <a href="{{ route('admin.orders.index') }}" class="hover:text-espresso-900 hover:underline">Pesanan Pelanggan</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="text-espresso-900">{{ $order->order_number }}</span>
        </nav>

        <div class="flex flex-wrap items-center gap-3">
            <h1 class="break-anywhere font-display text-3xl font-semibold tracking-tight text-espresso-900">{{ $order->order_number }}</h1>
            <x-order-status :status="$order->status" />
        </div>

        <p class="mt-2 text-sm text-espresso-800/60">
            {{ $order->created_at->translatedFormat('d M Y, H:i') }} &middot; {{ $order->paymentMethodLabel() }}
        </p>
    </x-slot>

    <div class="grid gap-8 lg:grid-cols-3 lg:items-start">
        <div class="space-y-6 lg:col-span-2">
            {{-- Items. Read the order snapshots, never the live product rows. --}}
            <section class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Produk</th>
                            <th scope="col" class="text-end">Harga</th>
                            <th scope="col" class="text-end">Qty</th>
                            <th scope="col" class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="font-medium text-espresso-900">{{ $item->product_name }}</td>
                                <td class="text-end tabular-nums">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-end tabular-nums">{{ $item->quantity }}</td>
                                <td class="text-end font-medium tabular-nums text-espresso-900">{{ $item->formattedSubtotal() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-parchment-200 bg-parchment-50/70">
                            <td colspan="3" class="px-5 py-4 text-end font-semibold text-espresso-900">Total</td>
                            <td class="px-5 py-4 text-end font-display text-lg font-semibold tabular-nums text-espresso-900">{{ $order->formattedTotal() }}</td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            {{-- Status form. No state machine: any of the five is allowed, but the
                 restock side effect of `cancelled` is spelled out in the copy. --}}
            <section class="card p-6">
                <h2 class="font-display text-lg font-semibold tracking-tight text-espresso-900">Ubah status</h2>

                @if ($order->status === 'cancelled')
                    <div class="mt-4 flex items-start gap-2.5 rounded-xl border border-parchment-200 bg-parchment-50 p-3.5 text-sm text-espresso-800/70">
                        <x-icon name="alert" class="mt-0.5 shrink-0 text-cognac-600" />
                        <p>Pesanan ini sudah dibatalkan, jadi stok produknya sudah dikembalikan.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-5 flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PATCH')

                    <div class="min-w-[14rem] flex-1">
                        <label for="status" class="label">Status baru</label>
                        <x-select id="status" name="status" required class="mt-1.5">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($order->status === $status)>
                                    {{ \App\Models\Order::statusLabels()[$status] ?? $status }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>

                    <x-primary-button><x-icon name="check" /> Simpan status</x-primary-button>
                </form>

                <p class="help mt-4 flex items-start gap-2">
                    <x-icon name="package" class="mt-0.5 shrink-0 text-cognac-600" />
                    Memilih <strong class="text-danger-600">Dibatalkan</strong> akan mengembalikan stok produk ke katalog.
                    Status lain tidak menyentuh stok.
                </p>
            </section>
        </div>

        <div class="space-y-6">
            <section class="card p-5">
                <h2 class="eyebrow">Detail pesanan</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    @foreach ([
                        'Pelanggan' => $order->customer_name,
                        'Telepon' => $order->customer_phone,
                        'Email' => $order->customer_email,
                        'Akun' => $order->user?->email ?? '-',
                        'Metode bayar' => $order->paymentMethodLabel(),
                    ] as $label => $value)
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="shrink-0 text-espresso-800/55">{{ $label }}</dt>
                            <dd class="break-anywhere text-end font-medium text-espresso-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-4 border-t border-parchment-200 pt-4">
                    <p class="text-xs text-espresso-800/55">Alamat pengiriman</p>
                    <p class="break-anywhere mt-1.5 text-sm leading-relaxed text-espresso-800/75">{{ $order->shipping_address }}</p>
                </div>

                @if ($order->note)
                    <div class="mt-4 border-t border-parchment-200 pt-4">
                        <p class="text-xs text-espresso-800/55">Catatan pelanggan</p>
                        <p class="break-anywhere mt-1.5 text-sm leading-relaxed text-espresso-800/75">{{ $order->note }}</p>
                    </div>
                @endif
            </section>

            <div class="flex items-start gap-2.5 rounded-2xl border px-4 py-3.5 text-sm {{ $order->payment_method === 'cod' ? 'border-info-100 bg-info-50 text-info-700' : 'border-warning-100 bg-warning-50 text-warning-700' }}">
                <x-icon name="alert" class="mt-0.5 shrink-0" />
                <p>
                    @if ($order->payment_method === 'cod')
                        COD. Pembayaran diterima saat barang diantar, jadi tidak ada tombol bayar untuk pelanggan.
                    @else
                        Pembayaran disimulasikan dari halaman pesanan pelanggan. Tidak ada transfer nyata.
                    @endif
                </p>
            </div>
        </div>
    </div>
</x-app-layout>