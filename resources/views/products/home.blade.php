<x-app-layout title="Tas &amp; Dompet Kulit">
    {{-- Hero ------------------------------------------------------------------}}
    <section class="relative overflow-hidden">
        <div class="page-container grid items-center gap-12 py-14 lg:grid-cols-2 lg:py-20">
            <div class="fade-rise">
                <p class="eyebrow">Dikoulder tangan di Indonesia</p>

                <h1 class="mt-4 font-display text-4xl font-semibold leading-[1.08] tracking-tight text-espresso-900 sm:text-5xl">
                    Tas dan dompet kulit<br>
                    yang <span class="text-cognac-600">tahan decades</span>, bukan satu musim.
                </h1>

                <p class="mt-5 max-w-lg text-base leading-relaxed text-espresso-800/65">
                    Kami memilih kulit sapi yang di-vegetable-tanned satu per satu,
                    lalu menjahitnya dengan tangan. Kalau jahitannya rontong, kami perbaiki — seumur pakai.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('products.catalog') }}" class="btn btn-primary btn-lg">
                        <x-icon name="bag" /> Lihat Katalog
                    </a>
                    <a href="#kategori" class="btn btn-outline btn-lg">
                        Pilih Kategori <x-icon name="chevron-down" />
                    </a>
                </div>

                <dl class="mt-10 grid max-w-md grid-cols-3 gap-6 border-t border-parchment-200 pt-6">
                    @foreach ([
                        ['value' => '100%', 'label' => 'Kulit asli'],
                        ['value' => '1 th', 'label' => 'Garansi jahit'],
                        ['value' => '24', 'label' => 'Kota dikirim'],
                    ] as $stat)
                        <div>
                            <dt class="font-display text-2xl font-semibold text-espresso-900">{{ $stat['value'] }}</dt>
                            <dd class="mt-0.5 text-xs text-espresso-800/55">{{ $stat['label'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- Stitched frame standing in for a hero photo; no stock art yet. --}}
            <div class="fade-rise relative" style="animation-delay:.1s">
                <div class="stitch-frame relative aspect-[4/5] bg-parchment-100">
                    <div class="absolute inset-6 flex items-center justify-center">
                        <div class="text-center">
                            <span class="mx-auto flex size-20 items-center justify-center rounded-3xl bg-espresso-800 text-parchment-100 shadow-lift">
                                <x-icon name="bag" class="size-10" />
                            </span>
                            <p class="mt-2 font-display text-xl font-semibold text-espresso-900">Kulit yang makin bagus seiring dipakai</p>
                            <p class="mt-2 text-sm text-espresso-800/55">Foto produk asli menyusul.</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-5 -left-5 hidden rounded-2xl border border-parchment-200 bg-white px-5 py-4 shadow-card sm:block">
                    <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-espresso-600">
                        <x-icon name="shield" class="text-cognac-600" /> Garansi
                    </p>
                    <p class="mt-1 text-sm text-espresso-800/60">Jahitan rontong, kami jahit ulang.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Categories ------------------------------------------------------------}}
    @if ($categories->isNotEmpty())
        <section id="kategori" class="scroll-mt-24 border-y border-parchment-200 bg-white/60 py-14">
            <div class="page-container">
                <p class="eyebrow">Kategori</p>
                <h2 class="section-title mt-2">Mulai dari bentuknya</h2>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categories as $category)
                        <a href="{{ route('products.catalog', ['category' => $category->slug]) }}"
                           class="card card-hover group flex items-center justify-between gap-4 p-6">
                            <span>
                                <span class="block font-display text-lg font-semibold text-espresso-900">{{ $category->name }}</span>
                                <span class="mt-1 block text-sm text-espresso-800/55">
                                    {{ $category->products()->where('is_active', true)->count() }} produk
                                </span>
                            </span>

                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-parchment-100 text-espresso-700 transition group-hover:bg-cognac-100 group-hover:text-cognac-700">
                                <x-icon name="arrow-right" />
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured --------------------------------------------------------------}}
    <section class="py-14">
        <div class="page-container">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Baru masuk</p>
                    <h2 class="section-title mt-2">Produk pilihan</h2>
                </div>

                <a href="{{ route('products.catalog') }}" class="btn btn-ghost btn-sm">
                    Lihat semua <x-icon name="arrow-right" />
                </a>
            </div>

            @if ($featured->isEmpty())
                <div class="card mt-8 p-12 text-center">
                    <p class="font-display text-lg font-semibold text-espresso-900">Belum ada produk</p>
                    <p class="mt-2 text-sm text-espresso-800/60">
                        Admin belum menambahkan produk. <a href="{{ route('admin.products.create') }}" class="font-semibold text-cognac-600 hover:underline">Tambah sekarang</a>.
                    </p>
                </div>
            @else
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featured as $product)
                        @include('products._card', ['product' => $product])
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Trust bar --------------------------------------------------------------}}
    <section class="border-t border-parchment-200 bg-white/60 py-14">
        <div class="page-container grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['shield', 'Kulit asli pilihan', 'Kami hanya menjual genuine leather. Kulit sintetis ditandai jelas, tidak disamarkan.'],
                ['truck', 'Kirim se-Indonesia', 'Sama dengan harga ongkir di checkout. Tidak ada biaya tersembunyi di akhir.'],
                ['card', 'Bayar sesuai pilihanmu', 'Transfer bank, e-wallet, QRIS, atau COD. Semua instruksi muncul sebelum bayar.'],
                ['package', 'Stok selalu jujur', 'Stok turun begitu pesanan dibuat, jadi angka di halaman ini tidak akan bohong.'],
            ] as $trust)
                <div>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-cognac-50 text-cognac-600">
                        <x-icon :name="$trust[0]" class="size-5" />
                    </span>
                    <h3 class="mt-4 font-semibold text-espresso-900">{{ $trust[1] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-espresso-800/60">{{ $trust[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>
</x-app-layout>