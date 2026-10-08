<x-app-layout title="Panel Admin">
    <x-slot name="header">
        <p class="eyebrow">Administrasi</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Panel Admin</h1>
        <p class="mt-2 text-sm text-espresso-800/60">
            Ringkasan katalog, pesanan, dan pengaturan pembayaran.
        </p>
    </x-slot>

    {{-- Stat cards. Icons make the four numbers scannable without reading labels. --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['icon' => 'bag', 'label' => 'Produk aktif', 'value' => \App\Models\Product::where('is_active', true)->count(), 'hint' => \App\Models\Product::where('is_active', false)->count().' nonaktif'],
                ['icon' => 'filter', 'label' => 'Kategori', 'value' => \App\Models\Category::count(), 'hint' => 'Kelompok katalog'],
                ['icon' => 'package', 'label' => 'Pesanan menunggu', 'value' => \App\Models\Order::where('status', 'pending')->count(), 'hint' => 'Perlu diproses'],
                ['icon' => 'card', 'label' => 'Total pendapatan', 'value' => 'Rp '.number_format(\App\Models\Order::whereIn('status', ['paid', 'shipped', 'completed'])->sum('total_amount'), 0, ',', '.'), 'hint' => 'Dari pesanan lunas'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm text-espresso-800/60">{{ $card['label'] }}</p>
                        <p class="mt-1.5 truncate font-display text-2xl font-semibold tracking-tight text-espresso-900">
                            {{ $card['value'] }}
                        </p>
                        <p class="mt-1 text-xs text-espresso-800/45">{{ $card['hint'] }}</p>
                    </div>

                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-cognac-50 text-cognac-600">
                        <x-icon :name="$card['icon']" />
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-10">
        <h2 class="eyebrow">Menu</h2>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['icon' => 'package', 'route' => 'admin.orders.index', 'title' => 'Kelola Pesanan', 'desc' => 'Lihat, ubah status, batalkan pesanan.'],
                ['icon' => 'bag', 'route' => 'admin.products.index', 'title' => 'Kelola Produk', 'desc' => 'Tambah produk, upload foto, atur stok.'],
                ['icon' => 'filter', 'route' => 'admin.categories.index', 'title' => 'Kelola Kategori', 'desc' => 'Kelompokkan produk katalog.'],
                ['icon' => 'sliders', 'route' => 'admin.settings.payment.edit', 'title' => 'Pengaturan Bayar', 'desc' => 'Rekening bank, e-wallet, kode QRIS.'],
            ] as $link)
                <a href="{{ route($link['route']) }}" class="card card-hover group flex items-start gap-3.5 p-5">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-parchment-100 text-espresso-700 transition group-hover:bg-cognac-50 group-hover:text-cognac-600">
                        <x-icon :name="$link['icon']" />
                    </span>

                    <span class="min-w-0">
                        <span class="block font-semibold text-espresso-900">{{ $link['title'] }}</span>
                        <span class="mt-1 block text-sm leading-relaxed text-espresso-800/60">{{ $link['desc'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>