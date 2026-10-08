<x-app-layout title="Keranjang">
    <x-slot name="header">
        <p class="eyebrow">Langkah 1 dari 3</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Keranjang</h1>
        <p class="mt-2 text-sm text-espresso-800/60">
            @if ($rows->isEmpty())
                Belum ada barang yang kamu pilih.
            @else
                {{ collect($rows)->sum(fn ($row) => $row['quantity']) }} barang, siap dikirim.
            @endif
        </p>
    </x-slot>

    @if ($rows->isEmpty())
        <div class="card flex flex-col items-center gap-4 px-6 py-16 text-center">
            <span class="flex size-16 items-center justify-center rounded-2xl bg-parchment-100 text-espresso-600">
                <x-icon name="cart" class="size-7" />
            </span>
            <p class="text-espresso-800/60">Keranjangmu masih kosong.</p>
            <a href="{{ route('products.catalog') }}" class="btn btn-primary mt-1">
                <x-icon name="bag" /> Mulai belanja
            </a>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-3 lg:items-start">
            <div class="space-y-4 lg:col-span-2">
                @foreach ($rows as $row)
                    @php $product = $row['product']; @endphp
                    <div class="card flex gap-4 p-4">
                        <a href="{{ route('products.show', $product) }}" class="card-media size-24 shrink-0 overflow-hidden rounded-xl">
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                                 loading="lazy"
                                 class="size-full {{ $product->hasImage() ? 'object-cover' : 'object-contain' }}">
                        </a>

                        <div class="min-w-0 flex-1">
                            <p class="eyebrow">{{ $product->category?->name }}</p>

                            <a href="{{ route('products.show', $product) }}"
                               class="mt-1 block font-display text-base font-semibold leading-snug text-espresso-900 hover:underline">
                                {{ $product->name }}
                            </a>

                            <p class="mt-1 text-sm text-espresso-800/60">{{ $product->formattedPrice() }} / pcs</p>

                            {{-- Inline stepper: no JS required, +/- submit the same PATCH. --}}
                            <form method="POST" action="{{ route('cart.update', $product) }}"
                                  class="mt-3 flex items-center gap-3">
                                @csrf
                                @method('PATCH')

                                <div class="flex h-9 items-center rounded-lg border border-parchment-200 bg-white">
                                    <button type="submit" name="quantity" value="{{ $row['quantity'] - 1 }}"
                                            @disabled($row['quantity'] <= 1)
                                            aria-label="Kurangi jumlah {{ $product->name }}"
                                            class="flex size-8 items-center justify-center rounded-l-lg text-espresso-700 transition hover:bg-parchment-100 disabled:opacity-30">
                                        <x-icon name="minus" class="size-4" />
                                    </button>

                                    <output class="w-9 text-center text-sm font-semibold tabular-nums text-espresso-900">{{ $row['quantity'] }}</output>

                                    <button type="submit" name="quantity" value="{{ $row['quantity'] + 1 }}"
                                            @disabled($row['quantity'] >= $product->stock)
                                            aria-label="Tambah jumlah {{ $product->name }}"
                                            class="flex size-8 items-center justify-center rounded-r-lg text-espresso-700 transition hover:bg-parchment-100 disabled:opacity-30">
                                        <x-icon name="plus" class="size-4" />
                                    </button>
                                </div>

                                <noscript>
                                    <label class="sr-only" for="qty-{{ $product->id }}">Jumlah {{ $product->name }}</label>
                                    <input id="qty-{{ $product->id }}" type="number" name="quantity" value="{{ $row['quantity'] }}"
                                           min="1" max="{{ $product->stock }}" class="field w-20">
                                    <button class="btn btn-outline btn-sm">Ubah</button>
                                </noscript>

                                <span class="help hidden sm:inline">Maks {{ $product->stock }}</span>
                            </form>
                        </div>

                        <div class="flex shrink-0 flex-col items-end justify-between gap-3 text-end">
                            <p class="font-display text-base font-semibold text-espresso-900">
                                Rp {{ number_format($row['subtotal'], 0, ',', '.') }}
                            </p>

                            <form method="POST" action="{{ route('cart.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <button class="flex items-center gap-1.5 text-xs text-espresso-800/55 transition hover:text-danger-600">
                                    <x-icon name="trash" class="size-4" /> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <a href="{{ route('products.catalog') }}" class="btn btn-ghost btn-sm">
                        <x-icon name="chevron-left" /> Lanjut belanja
                    </a>

                    <form method="POST" action="{{ route('cart.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-xs text-espresso-800/55 transition hover:text-danger-600 hover:underline">
                            Kosongkan keranjang
                        </button>
                    </form>
                </div>
            </div>

            {{-- Summary ------------------------------------------------------------ --}}
            <div class="card p-6 lg:sticky lg:top-24">
                <h2 class="font-display text-lg font-semibold tracking-tight text-espresso-900">Ringkasan</h2>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-espresso-800/60">Subtotal ({{ collect($rows)->sum(fn ($row) => $row['quantity']) }} barang)</dt>
                        <dd class="font-medium text-espresso-900">Rp {{ number_format($total, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-espresso-800/60">Ongkir</dt>
                        <dd class="text-espresso-800/55">Dihitung terpisah oleh penjual</dd>
                    </div>

                    <div class="flex justify-between border-t border-parchment-200 pt-4">
                        <dt class="font-semibold text-espresso-900">Total</dt>
                        <dd class="font-display text-xl font-semibold text-espresso-900">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>

                <a href="{{ route('checkout.create') }}" class="btn btn-primary btn-lg mt-6 w-full">
                    Lanjut ke Checkout <x-icon name="arrow-right" />
                </a>

                <p class="help mt-4 text-center">
                    Harga dan nama produk disimpan ulang saat pesanan dibuat, jadi tidak ikut berubah.
                </p>
            </div>
        </div>
    @endif
</x-app-layout>