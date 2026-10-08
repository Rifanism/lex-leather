<x-app-layout title="Produk">
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Administrasi</p>
                <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Produk</h1>
                <p class="mt-2 text-sm text-espresso-800/60">{{ $products->total() }} produk di katalog.</p>
            </div>

            <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><x-icon name="plus" /> Tambah Produk</a>
        </div>
    </x-slot>

    <form method="GET" class="card mb-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[13rem] flex-1">
            <label for="q" class="label">Cari</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama produk…" class="field mt-1.5">
        </div>

        <div class="min-w-[11rem]">
            <label for="category" class="label">Kategori</label>
            <x-select id="category" name="category" class="mt-1.5">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category'] ?? null) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-select>
        </div>

        <div class="min-w-[10rem]">
            <label for="status" class="label">Status</label>
            <x-select id="status" name="status" class="mt-1.5">
                <option value="">Semua status</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </x-select>
        </div>

        <x-primary-button><x-icon name="filter" /> Terapkan</x-primary-button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Produk</th>
                    <th scope="col">Kategori</th>
                    <th scope="col" class="text-end">Harga</th>
                    <th scope="col" class="text-end">Stok</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->imageUrl() }}" alt="" loading="lazy"
                                     class="size-12 shrink-0 rounded-lg bg-parchment-100 object-cover {{ $product->hasImage() ? '' : 'object-contain' }}">
                                <div class="min-w-0">
                                    <p class="font-medium text-espresso-900">{{ $product->name }}</p>
                                    <p class="mt-0.5 text-xs text-espresso-800/45">{{ $product->materialLabel() }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-espresso-800/60">{{ $product->category?->name }}</td>
                        <td class="text-end font-medium tabular-nums text-espresso-900">{{ $product->formattedPrice() }}</td>
                        <td class="text-end tabular-nums {{ $product->isInStock() ? '' : 'font-semibold text-danger-600' }}">
                            {{ $product->stock }}
                        </td>
                        <td>
                            <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-neutral' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            {{-- Alpine confirms inline instead of a native confirm() dialog,
                                 which cannot be styled and blocks the whole page. --}}
                            <div class="flex items-center justify-end gap-1" x-data="{ confirm: false }">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-ghost btn-sm">
                                    <x-icon name="edit" class="size-4" /> Ubah
                                </a>

                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button type="button" x-on:click="confirm = true" class="btn btn-ghost btn-sm text-danger-600 hover:bg-danger-50">
                                        <x-icon name="trash" class="size-4" /> <span>Hapus</span>
                                    </button>

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
                                            <h3 class="mt-4 font-display text-lg font-semibold text-espresso-900">Hapus produk ini?</h3>
                                            <p class="mt-2 text-sm leading-relaxed text-espresso-800/65">
                                                @if ($product->orderItems()->exists())
                                    Produk ini sudah pernah dipesan, jadi datanya tidak bisa dihapus permanen. Nonaktifkan saja supaya hilang dari katalog.
                                @else
                                    <span class="font-medium text-espresso-900">{{ $product->name }}</span> akan dihapus permanen.
                                @endif
                                            </p>

                                            <div class="mt-6 flex justify-end gap-2.5">
                                                <button type="button" x-on:click="confirm = false" class="btn btn-outline btn-sm">Batal</button>
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    {{ $product->orderItems()->exists() ? 'Nonaktifkan' : 'Hapus' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center">
                            <p class="font-display text-base font-semibold text-espresso-900">Belum ada produk</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm mt-3">Tambah produk pertama</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-8">{{ $products->links() }}</div>
</x-app-layout>