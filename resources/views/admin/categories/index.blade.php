<x-app-layout title="Kategori">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Administrasi</p>
                <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Kategori</h1>
                <p class="mt-2 text-sm text-espresso-800/60">{{ $categories->count() }} kategori mengelompokkan katalog.</p>
            </div>

            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><x-icon name="plus" /> Tambah Kategori</a>
        </div>
    </x-slot>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Slug</th>
                    <th scope="col" class="text-end">Produk</th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>
                            <p class="font-medium text-espresso-900">{{ $category->name }}</p>
                        </td>
                        <td class="font-mono text-xs text-espresso-800/55">{{ $category->slug }}</td>
                        <td class="text-end tabular-nums text-espresso-800/70">{{ $category->products_count }}</td>
                        <td>
                            {{-- products_count decides whether a delete is possible: a populated
                                 category hits the restrictive products.category_id FK. --}}
                            <div class="flex items-center justify-end gap-1" x-data="{ confirm: false }">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-ghost btn-sm">
                                    <x-icon name="edit" class="size-4" /> Ubah
                                </a>

                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                    @csrf
                                    @method('DELETE')

                                    @if ($category->products_count > 0)
                                        <button type="button" disabled title="Kategori ini masih ada produknya"
                                                class="btn btn-ghost btn-sm text-espresso-800/30">
                                            <x-icon name="trash" class="size-4" /> <span>Hapus</span>
                                        </button>
                                    @else
                                        <button type="button" x-on:click="confirm = true" class="btn btn-ghost btn-sm text-danger-600 hover:bg-danger-50">
                                            <x-icon name="trash" class="size-4" /> <span>Hapus</span>
                                        </button>
                                    @endif

                                    <div x-show="confirm" x-cloak
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                        <div class="absolute inset-0 bg-espresso-950/50 backdrop-blur-sm" x-on:click="confirm = false"></div>

                                        <div class="relative w-full max-w-sm rounded-3xl border border-parchment-200 bg-white p-6 shadow-lift">
                                            <span class="flex size-11 items-center justify-center rounded-xl bg-danger-50 text-danger-600">
                                                <x-icon name="trash" />
                                            </span>
                                            <h3 class="mt-4 font-display text-lg font-semibold text-espresso-900">Hapus kategori ini?</h3>
                                            <p class="mt-2 text-sm leading-relaxed text-espresso-800/65">
                                                <span class="font-medium text-espresso-900">{{ $category->name }}</span> akan dihapus permanen.
                                                Tidak ada produk yang kehilangan kategorinya karena kolom ini masih kosong.
                                            </p>

                                            <div class="mt-6 flex justify-end gap-2.5">
                                                <button type="button" x-on:click="confirm = false" class="btn btn-outline btn-sm">Batal</button>
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-14 text-center">
                            <p class="font-display text-base font-semibold text-espresso-900">Belum ada kategori</p>
                            <p class="mt-1 text-sm text-espresso-800/55">Buat kategori dulu sebelum menambahkan produk.</p>
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm mt-3">Tambah kategori pertama</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>