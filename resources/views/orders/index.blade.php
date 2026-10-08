<x-app-layout title="Pesanan Saya">
    <x-slot name="header">
        <p class="eyebrow">Riwayat</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Pesanan Saya</h1>
        <p class="mt-2 text-sm text-espresso-800/60">
            @if ($orders->isEmpty())
                Semua pesananmu akan muncul di sini.
            @else
                {{ $orders->count() }} pesanan terakhir.
            @endif
        </p>
    </x-slot>

    @if ($orders->isEmpty())
        <div class="card flex flex-col items-center gap-4 px-6 py-16 text-center">
            <span class="flex size-16 items-center justify-center rounded-2xl bg-parchment-100 text-espresso-600">
                <x-icon name="package" class="size-7" />
            </span>
            <p class="text-espresso-800/60">Belum ada pesanan.</p>
            <a href="{{ route('products.catalog') }}" class="btn btn-primary mt-1">
                <x-icon name="bag" /> Mulai belanja
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="card card-hover block p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="break-anywhere font-display text-base font-semibold tracking-tight text-espresso-900">
                                {{ $order->order_number }}
                            </p>
                            <p class="mt-1 text-xs text-espresso-800/55">
                                {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                            </p>
                        </div>

                        <x-order-status :status="$order->status" />
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-parchment-100 pt-4">
                        <p class="text-xs text-espresso-800/55">
                            {{ $order->paymentMethodLabel() }}
                            &middot; {{ $order->items->sum('quantity') }} item
                        </p>

                        <p class="font-display text-lg font-semibold text-espresso-900">
                            {{ $order->formattedTotal() }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-app-layout>