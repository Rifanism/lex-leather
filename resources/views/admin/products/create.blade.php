<x-app-layout title="Tambah Produk">
    <x-slot name="header">
        <nav class="mb-3 flex items-center gap-2 text-sm text-espresso-800/55" aria-label="Breadcrumb">
            <a href="{{ route('admin.products.index') }}" class="hover:text-espresso-900 hover:underline">Produk</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="text-espresso-900">Tambah</span>
        </nav>

        <h1 class="font-display text-3xl font-semibold tracking-tight text-espresso-900">Tambah Produk</h1>
        <p class="mt-2 text-sm text-espresso-800/60">Foto, harga, dan stok bisa kamu ubah nanti.</p>
    </x-slot>

    <div class="max-w-3xl">
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.products._form')
            </form>
        </div>
    </div>
</x-app-layout>
