<x-app-layout :title="$product->name">
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-sm text-espresso-800/55" aria-label="Breadcrumb">
        <a href="{{ route('products.catalog') }}" class="hover:text-espresso-900 hover:underline">Katalog</a>
        <x-icon name="chevron-right" class="size-3.5" />
        @if ($product->category)
            <a href="{{ route('products.catalog', ['category' => $product->category->slug]) }}" class="hover:text-espresso-900 hover:underline">
                {{ $product->category->name }}
            </a>
            <x-icon name="chevron-right" class="size-3.5" />
        @endif
        <span class="text-espresso-900">{{ $product->name }}</span>
    </nav>

    <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
        <div class="card overflow-hidden">
            <div class="card-media aspect-square">
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                     class="size-full {{ $product->hasImage() ? 'object-cover' : 'object-contain' }}">
            </div>
        </div>

        {{-- Sticky on desktop so the buy button follows you down a long description. --}}
        <div class="lg:sticky lg:top-24">
            <div class="flex flex-wrap items-center gap-2">
                @if ($product->category)
                    <a href="{{ route('products.catalog', ['category' => $product->category->slug]) }}" class="badge badge-accent">
                        {{ $product->category->name }}
                    </a>
                @endif

                <span class="badge {{ $product->material === 'genuine' ? 'badge-accent' : 'badge-neutral' }}">
                    {{ $product->materialLabel() }}
                </span>

                <span class="badge {{ $product->isInStock() ? 'badge-success' : 'badge-danger' }}">
                    {{ $product->isInStock() ? 'Stok '.$product->stock : 'Stok habis' }}
                </span>
            </div>

            <h1 class="mt-4 font-display text-4xl font-semibold leading-tight tracking-tight text-espresso-900">
                {{ $product->name }}
            </h1>

            <p class="mt-4 font-display text-3xl font-semibold text-espresso-900">
                {{ $product->formattedPrice() }}
            </p>
            <p class="help mt-1">Belum termasuk ongkir. Ongkos muncul di langkah checkout.</p>

            <div class="mt-6 border-t border-parchment-200 pt-6">
                <h2 class="eyebrow">Tentang produk ini</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-espresso-800/75">
                    {{ $product->description }}
                </p>
            </div>

            <div class="mt-8 border-t border-parchment-200 pt-6">
                @auth
                    @if ($product->isInStock())
                        <form method="POST" action="{{ route('cart.store') }}" x-data="{ qty: 1 }">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            {{-- Alpine owns the visible value; the hidden input above is what
                                 actually posts, so the stepper works without JS too. --}}
                            <input type="hidden" name="quantity" :value="qty">

                            <div class="flex flex-wrap items-end gap-4">
                                <div>
                                    <span class="label">Jumlah</span>
                                    <div class="mt-1.5 flex h-[42px] items-center rounded-xl border border-parchment-200 bg-white shadow-sm">
                                        <button type="button"
                                                @click="qty = Math.max(1, qty - 1)"
                                                :disabled="qty <= 1"
                                                class="flex size-10 items-center justify-center rounded-l-xl text-espresso-700 transition hover:bg-parchment-100 disabled:opacity-30"
                                                aria-label="Kurangi jumlah">
                                            <x-icon name="minus" />
                                        </button>

                                        <output class="w-10 text-center text-sm font-semibold tabular-nums text-espresso-900" aria-live="polite" x-text="qty">1</output>

                                        <button type="button"
                                                @click="qty = Math.min({{ $product->stock }}, qty + 1)"
                                                :disabled="qty >= {{ $product->stock }}"
                                                class="flex size-10 items-center justify-center rounded-r-xl text-espresso-700 transition hover:bg-parchment-100 disabled:opacity-30"
                                                aria-label="Tambah jumlah">
                                            <x-icon name="plus" />
                                        </button>
                                    </div>
                                    <p class="help mt-1.5">Maksimal {{ $product->stock }}</p>
                                    <x-input-error :messages="$errors->get('quantity')" />
                                </div>

                                <x-primary-button class="h-[42px] flex-1 sm:flex-none">
                                    <x-icon name="cart" /> Tambah ke Keranjang
                                </x-primary-button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-start gap-3 rounded-2xl border border-danger-100 bg-danger-50 px-4 py-3.5 text-sm text-danger-700">
                            <x-icon name="alert" class="mt-0.5 shrink-0" />
                            <p>Produk ini sedang habis. Cek lagi nanti, stoknya kami perbarui tiap hari.</p>
                        </div>
                    @endif
                @else
                    <div class="rounded-2xl border border-parchment-200 bg-white p-5">
                        <p class="text-sm text-espresso-800/70">Masuk dulu untuk menambah ke keranjang.</p>
                        <div class="mt-4 flex gap-3">
                            <a href="{{ route('login') }}" class="btn btn-primary flex-1 sm:flex-none">Masuk</a>
                            <a href="{{ route('register') }}" class="btn btn-outline flex-1 sm:flex-none">Daftar</a>
                        </div>
                    </div>
                @endauth
            </div>

            <dl class="mt-8 grid gap-4 border-t border-parchment-200 pt-6 sm:grid-cols-3">
                @foreach ([
                    ['shield', 'Garansi 1 tahun', 'Jahitan rontong dijahit ulang gratis.'],
                    ['truck', 'Kirim 1-3 hari', 'Setelah pesanan dikonfirmasi.'],
                    ['card', 'COD tersedia', 'Bayar saat barang sampai.'],
                ] as $perk)
                    <div class="flex items-start gap-2.5">
                        <x-icon :name="$perk[0]" class="mt-0.5 shrink-0 text-cognac-600" />
                        <div>
                            <dt class="text-sm font-semibold text-espresso-900">{{ $perk[1] }}</dt>
                            <dd class="mt-0.5 text-xs leading-relaxed text-espresso-800/55">{{ $perk[2] }}</dd>
                        </div>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</x-app-layout>