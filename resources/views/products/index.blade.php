<x-app-layout title="Katalog">
    <x-slot name="header">
        <p class="eyebrow">Katalog</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">
            {{ $products->total() }} produk
        </h1>
        <p class="mt-2 text-sm text-espresso-800/60">Tas, dompet, dan aksesori kulit yang sedang kami kerjakan.</p>
    </x-slot>

    <div class="grid gap-8 lg:grid-cols-[17rem_1fr] lg:items-start">
        {{-- Filters ------------------------------------------------------------- --}}
        <form method="GET" action="{{ route('products.catalog') }}"
              class="card p-5 lg:sticky lg:top-24">
            <div class="flex items-center justify-between gap-3">
                <h2 class="flex items-center gap-2 font-semibold text-espresso-900">
                    <x-icon name="filter" class="text-cognac-600" /> Saring
                </h2>

                @if (array_filter($filters))
                    <a href="{{ route('products.catalog') }}" class="text-xs font-semibold text-cognac-600 hover:underline">Reset</a>
                @endif
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <label for="q" class="label">Cari</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nama produk…" class="field mt-1.5">
                </div>

                <div>
                    <label for="category" class="label">Kategori</label>
                    <x-select id="category" name="category" class="mt-1.5">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <label for="material" class="label">Material</label>
                    <x-select id="material" name="material" class="mt-1.5">
                        <option value="">Semua material</option>
                        @foreach ($materials as $material)
                            <option value="{{ $material }}" @selected(($filters['material'] ?? '') === $material)>
                                {{ \App\Models\Product::materialLabels()[$material] }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <span class="label">Rentang harga</span>
                    <div class="mt-1.5 flex items-center gap-2">
                        <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}"
                               placeholder="Min" min="0" aria-label="Harga minimum" class="field">
                        <span class="text-espresso-800/40">-</span>
                        <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}"
                               placeholder="Max" min="0" aria-label="Harga maksimum" class="field">
                    </div>
                </div>

                <div>
                    <label for="sort" class="label">Urutkan</label>
                    <x-select id="sort" name="sort" class="mt-1.5">
                        @foreach (['latest' => 'Terbaru', 'price_asc' => 'Harga termurah', 'price_desc' => 'Harga termahal', 'name' => 'Nama A-Z'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest') === $value)>{{ $label }}</option>
                        @endforeach
                    </x-select>
                </div>

                <x-primary-button class="w-full"><x-icon name="filter" /> Terapkan</x-primary-button>
            </div>
        </form>

        {{-- Results ------------------------------------------------------------- --}}
        <div>
            {{-- Active filter chips, so a narrowed result set is never a mystery. --}}
            @php
                $chips = array_filter([
                    ! empty($filters['q']) ? ['label' => $filters['q'], 'key' => 'q'] : null,
                    ! empty($filters['category']) ? ['label' => $categories->firstWhere('slug', $filters['category'])->name ?? $filters['category'], 'key' => 'category'] : null,
                    ! empty($filters['material']) ? ['label' => \App\Models\Product::materialLabels()[$filters['material']], 'key' => 'material'] : null,
                    isset($filters['min_price']) ? ['label' => 'Min '.number_format((int) $filters['min_price'], 0, ',', '.'), 'key' => 'min_price'] : null,
                    isset($filters['max_price']) ? ['label' => 'Max '.number_format((int) $filters['max_price'], 0, ',', '.'), 'key' => 'max_price'] : null,
                ]);
            @endphp

            @if ($chips)
                <div class="mb-6 flex flex-wrap items-center gap-2">
                    @foreach ($chips as $chip)
                        @php $remaining = collect($filters)->except($chip['key'])->filter(fn ($v) => $v !== null && $v !== '')->all(); @endphp
                        <a href="{{ route('products.catalog', $remaining) }}"
                           class="badge badge-accent gap-1.5 py-1.5 transition hover:bg-cognac-100">
                            {{ $chip['label'] }} <x-icon name="x" class="size-3.5" />
                            <span class="sr-only">Hapus filter ini</span>
                        </a>
                    @endforeach

                    <a href="{{ route('products.catalog') }}" class="text-xs text-espresso-800/55 hover:text-espresso-900 hover:underline">Hapus semua</a>
                </div>
            @endif

            @if ($products->isEmpty())
                <div class="card flex flex-col items-center gap-3 p-14 text-center">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-parchment-100 text-espresso-600">
                        <x-icon name="search" class="size-6" />
                    </span>
                    <p class="font-display text-lg font-semibold text-espresso-900">Tidak ada yang cocok</p>
                    <p class="max-w-sm text-sm text-espresso-800/60">
                        Coba longgarkan rentang harga atau hapus beberapa filter.
                    </p>
                    <a href="{{ route('products.catalog') }}" class="btn btn-outline btn-sm mt-1">Reset filter</a>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        @include('products._card', ['product' => $product])
                    @endforeach
                </div>

                <div class="mt-10">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>