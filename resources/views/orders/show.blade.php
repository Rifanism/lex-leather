<x-app-layout :title="'Pesanan '.$order->order_number">
    <nav class="mb-8 flex items-center gap-2 text-sm text-espresso-800/55" aria-label="Breadcrumb">
        <a href="{{ route('orders.index') }}" class="hover:text-espresso-900 hover:underline">Pesanan Saya</a>
        <x-icon name="chevron-right" class="size-3.5" />
        <span class="text-espresso-900">{{ $order->order_number }}</span>
    </nav>

    {{-- Progress timeline. cancelled short-circuits to a single terminal step. --}}
    @php
        $stages = ['pending' => 'Pesanan dibuat', 'paid' => 'Pembayaran diterima', 'shipped' => 'Dikirim', 'completed' => 'Selesai'];
        $current = array_search($order->status, array_keys($stages), true);
        $isCancelled = $order->status === 'cancelled';
    @endphp

    @if ($isCancelled)
        <div class="card mb-8 flex items-start gap-3 border-danger-100 bg-danger-50 p-5">
            <x-icon name="alert" class="mt-0.5 shrink-0 text-danger-600" />
            <div>
                <p class="font-semibold text-danger-700">Pesanan ini dibatalkan</p>
                <p class="mt-1 text-sm text-danger-700/80">Stok produk sudah dikembalikan ke etalase.</p>
            </div>
        </div>
    @else
        <ol class="card mb-8 flex flex-wrap gap-x-6 gap-y-4 p-5 sm:p-6">
            @foreach ($stages as $key => $label)
                @php $done = $current !== false && $loop->index <= $current; @endphp
                <li class="flex min-w-[8rem] flex-1 items-center gap-3">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $done ? 'bg-cognac-500 text-white' : 'border border-parchment-300 text-espresso-800/35' }}">
                        @if ($done)
                            <x-icon name="check" class="size-4" />
                        @else
                            <span class="text-xs font-semibold">{{ $loop->iteration }}</span>
                        @endif
                    </span>
                    <span class="text-sm {{ $done ? 'font-semibold text-espresso-900' : 'text-espresso-800/45' }}">{{ $label }}</span>
                </li>
            @endforeach
        </ol>
    @endif

    <div class="grid gap-8 lg:grid-cols-3 lg:items-start">
        <div class="space-y-6 lg:col-span-2">
            {{-- Items. Names and prices are the order snapshots, not the live product. --}}
            <section class="table-wrap">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-parchment-200 p-5">
                    <div>
                        <h1 class="break-anywhere font-display text-xl font-semibold tracking-tight text-espresso-900">{{ $order->order_number }}</h1>
                        <p class="mt-1 text-xs text-espresso-800/55">
                            {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                            &middot; {{ $order->paymentMethodLabel() }}
                        </p>
                    </div>
                    <x-order-status :status="$order->status" />
                </div>

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

            @if ($order->note)
                <section class="card p-5">
                    <h2 class="eyebrow">Catatan</h2>
                    <p class="break-anywhere mt-2 text-sm leading-relaxed text-espresso-800/75">{{ $order->note }}</p>
                </section>
            @endif
        </div>

        <div class="space-y-6">
            @if ($order->canBePaidByCustomer())
                {{-- Prepaid and still pending: the only place a customer can pay. --}}
                <section class="card border-warning-100 bg-warning-50 p-5">
                    <h2 class="flex items-center gap-2 font-display text-lg font-semibold tracking-tight text-warning-700">
                        <x-icon name="alert" /> Menunggu pembayaran
                    </h2>

                    <dl class="mt-4 space-y-2 text-sm text-warning-700">
                        @forelse ($setting->detailsFor($order->payment_method) as $detail)
                            <div class="flex items-baseline justify-between gap-3">
                                <dt>{{ $detail['label'] }}</dt>
                                <dd class="font-semibold text-end">{{ $detail['value'] }}</dd>
                            </div>
                        @empty
                            <p class="text-xs">Detail akun belum diisi admin, tapi kamu tetap bisa menyimulasikan pembayaran.</p>
                        @endforelse
                    </dl>

                    @if ($order->payment_method === 'qris' && $setting->qrisImageUrl())
                        <img src="{{ $setting->qrisImageUrl() }}" alt="Kode QRIS" class="mt-4 w-44 rounded-xl border border-warning-100 bg-white">
                    @endif

                    <form method="POST" action="{{ route('orders.pay', $order) }}" class="mt-5">
                        @csrf
                        <x-primary-button class="w-full"><x-icon name="check" /> Simulasikan pembayaran</x-primary-button>
                    </form>

                    <p class="help mt-3 text-warning-700/70">
                        Simulasi. Tidak ada uang sungguhan yang berpindah.
                    </p>
                </section>
            @elseif ($order->isPending() && $order->payment_method === 'cod')
                <section class="card p-5">
                    <h2 class="font-display text-lg font-semibold tracking-tight text-espresso-900">COD</h2>
                    <p class="mt-2 text-sm leading-relaxed text-espresso-800/65">
                        Siapkan <span class="font-semibold text-espresso-900">{{ $order->formattedTotal() }}</span>
                        saat barang diterima. Tidak ada langkah pembayaran sekarang.
                    </p>
                </section>
            @endif

            <section class="card p-5">
                <h2 class="eyebrow">Alamat pengiriman</h2>
                <address class="mt-3 space-y-1 text-sm not-italic leading-relaxed text-espresso-800/70">
                    <div class="font-semibold text-espresso-900">{{ $order->customer_name }}</div>
                    <div>{{ $order->customer_phone }}</div>
                    <div>{{ $order->customer_email }}</div>
                    <div class="break-anywhere mt-3 border-t border-parchment-100 pt-3">{{ $order->shipping_address }}</div>
                </address>
                <p class="help mt-4">
                    Salinan dari saat pesanan dibuat, terpisah dari profilmu.
                </p>
            </section>
        </div>
    </div>
</x-app-layout>