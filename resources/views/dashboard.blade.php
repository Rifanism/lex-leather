<x-app-layout title="Akun Saya">
    <x-slot name="header">
        <p class="eyebrow">Halo</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">
            {{ auth()->user()->name }}
        </h1>
        <p class="mt-2 text-sm text-espresso-800/60">Ringkasan pesanan dan pengaturan akunmu ada di sini.</p>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2">
        <a href="{{ route('orders.index') }}" class="card card-hover group flex items-start gap-4 p-6">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-cognac-50 text-cognac-600">
                <x-icon name="package" class="size-5" />
            </span>
            <span>
                <span class="block font-semibold text-espresso-900">Pesanan Saya</span>
                <span class="mt-1 block text-sm text-espresso-800/60">Cek status pengiriman dan riwayat pembayaran.</span>
            </span>
        </a>

        <a href="{{ route('profile.edit') }}" class="card card-hover group flex items-start gap-4 p-6">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-parchment-200 text-espresso-700">
                <x-icon name="user" class="size-5" />
            </span>
            <span>
                <span class="block font-semibold text-espresso-900">Profil Saya</span>
                <span class="mt-1 block text-sm text-espresso-800/60">Perbarui nama, nomor HP, dan alamat pengiriman.</span>
            </span>
        </a>

        <a href="{{ route('products.catalog') }}" class="card card-hover group flex items-start gap-4 p-6">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-parchment-200 text-espresso-700">
                <x-icon name="bag" class="size-5" />
            </span>
            <span>
                <span class="block font-semibold text-espresso-900">Lanjut Belanja</span>
                <span class="mt-1 block text-sm text-espresso-800/60">Koleksi kulit terbaru dan yang paling dicari.</span>
            </span>
        </a>

        <a href="{{ route('cart.index') }}" class="card card-hover group flex items-start gap-4 p-6">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-parchment-200 text-espresso-700">
                <x-icon name="cart" class="size-5" />
            </span>
            <span>
                <span class="block font-semibold text-espresso-900">Keranjang</span>
                <span class="mt-1 block text-sm text-espresso-800/60">Barang yang kamu sisihkan sebelumnya masih ada.</span>
            </span>
        </a>
    </div>
</x-app-layout>