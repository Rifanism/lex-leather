<footer class="border-t border-parchment-200 bg-white/60">
    {{-- Two columns fewer for an admin: Belanja is theirs-only-403 and their --}}
    {{-- account link lives under /admin, so the grid narrows to match.        --}}
    <div class="page-container grid gap-8 py-12 {{ $isAdmin ? 'sm:grid-cols-2 lg:grid-cols-3' : 'sm:grid-cols-2 lg:grid-cols-4' }}">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-xl bg-espresso-800 text-parchment-100">
                    <x-icon name="bag" />
                </span>
                <span class="font-display text-lg font-semibold tracking-tight text-espresso-900">
                    {{ config('app.name') }}
                </span>
            </div>
            <p class="mt-4 max-w-xs text-sm leading-relaxed text-espresso-800/60">
                Tas dan dompet kulit pilihan, dikerjakan tangan di Jogjakarta. Material
                yang jujur, garansi yang jelas.
            </p>
        </div>

        {{-- Hidden for admins: those three routes are a 403 for them. --}}
        @unless ($isAdmin)
            <div>
                <h3 class="eyebrow">Belanja</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-espresso-700">
                    <li><a href="{{ route('products.catalog') }}" class="hover:text-espresso-900 hover:underline">Katalog</a></li>
                    <li><a href="{{ route('products.catalog') }}?sort=latest" class="hover:text-espresso-900 hover:underline">Baru Masuk</a></li>
                    <li><a href="{{ route('cart.index') }}" class="hover:text-espresso-900 hover:underline">Keranjang</a></li>
                </ul>
            </div>
        @endunless

        <div>
            <h3 class="eyebrow">Akun</h3>
            <ul class="mt-4 space-y-2.5 text-sm text-espresso-700">
                @auth
                    @unless ($isAdmin)
                        <li><a href="{{ route('orders.index') }}" class="hover:text-espresso-900 hover:underline">Pesanan Saya</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="hover:text-espresso-900 hover:underline">Profil Saya</a></li>
                    @else
                        <li><a href="{{ route('admin.settings.account.edit') }}" class="hover:text-espresso-900 hover:underline">Akun Admin</a></li>
                    @endunless
                @else
                    <li><a href="{{ route('login') }}" class="hover:text-espresso-900 hover:underline">Masuk</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-espresso-900 hover:underline">Daftar</a></li>
                @endauth
            </ul>
        </div>

        <div>
            <h3 class="eyebrow">Jaminan</h3>
            <ul class="mt-4 space-y-3 text-sm text-espresso-700">
                <li class="flex items-start gap-2.5">
                    <x-icon name="shield" class="mt-0.5 text-cognac-600" />
                    <span>Garansi stitched 1 tahun</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <x-icon name="truck" class="mt-0.5 text-cognac-600" />
                    <span>Kirim se-Indonesia</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <x-icon name="card" class="mt-0.5 text-cognac-600" />
                    <span>Transfer, QRIS, atau COD</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="border-t border-parchment-200">
        <div class="page-container flex flex-col gap-2 py-5 text-xs text-espresso-800/50 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. Course project.</p>
            <p>Harga dalam rupiah, belum termasuk ongkir.</p>
        </div>
    </div>
</footer>