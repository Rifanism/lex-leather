<x-app-layout :title="'Ubah '.$product->name">
    <x-slot name="header">
        <nav class="mb-3 flex items-center gap-2 text-sm text-espresso-800/55" aria-label="Breadcrumb">
            <a href="{{ route('admin.products.index') }}" class="hover:text-espresso-900 hover:underline">Produk</a>
            <x-icon name="chevron-right" class="size-3.5" />
            <span class="text-espresso-900">Ubah</span>
        </nav>

        <h1 class="font-display text-3xl font-semibold tracking-tight text-espresso-900">{{ $product->name }}</h1>
    </x-slot>

    <div class="max-w-3xl">
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.products._form')
            </form>
        </div>
    </div>
</x-app-layout>
