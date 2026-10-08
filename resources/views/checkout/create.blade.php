<x-app-layout title="Checkout">
    <x-slot name="header">
        <p class="eyebrow">Langkah 2 dari 3</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Checkout</h1>
        <p class="mt-2 text-sm text-espresso-800/60">
            Cek ulang alamat dan pilih cara bayar. Stok produk langsung dipesan begitu kamu tekan tombol.
        </p>
    </x-slot>

    {{-- Numbered progress. Three steps, the last one is this page. --}}
    <ol class="mb-10 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
        @foreach ([
            ['n' => 1, 'label' => 'Keranjang', 'done' => true, 'url' => route('cart.index')],
            ['n' => 2, 'label' => 'Alamat & Bayar', 'done' => false, 'url' => null],
            ['n' => 3, 'label' => 'Selesai', 'done' => false, 'url' => null],
        ] as $step)
            <li class="flex items-center gap-3">
                @if ($step['done'])
                    <a href="{{ $step['url'] }}" class="flex items-center gap-2 text-espresso-800/55 transition hover:text-espresso-900">
                        <span class="flex size-7 items-center justify-center rounded-full bg-espresso-800 text-parchment-100">
                            <x-icon name="check" class="size-4" />
                        </span>
                        {{ $step['label'] }}
                    </a>
                @elseif ($step['n'] === 2)
                    <span class="flex items-center gap-2 font-semibold text-espresso-900">
                        <span class="flex size-7 items-center justify-center rounded-full bg-cognac-500 text-xs font-semibold text-white">2</span>
                        {{ $step['label'] }}
                    </span>
                @else
                    <span class="flex items-center gap-2 text-espresso-800/45">
                        <span class="flex size-7 items-center justify-center rounded-full border border-parchment-300 text-xs font-semibold">3</span>
                        {{ $step['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>

    <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-8 lg:grid-cols-3 lg:items-start">
        @csrf

        <div class="space-y-6 lg:col-span-2">
            {{-- 1. Recipient ------------------------------------------------------ --}}
            <section class="card p-6">
                <h2 class="flex items-center gap-2.5 font-display text-lg font-semibold tracking-tight text-espresso-900">
                    <span class="flex size-7 items-center justify-center rounded-full bg-espresso-800 text-xs font-semibold text-parchment-100">1</span>
                    Alamat &amp; penerima
                </h2>

                <div class="mt-5 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="customer_name" value="Nama Penerima" />
                            <x-text-input id="customer_name" name="customer_name" required maxlength="255" class="mt-1.5" :value="old('customer_name', $user->name)" />
                            <x-input-error :messages="$errors->get('customer_name')" />
                        </div>

                        <div>
                            <x-input-label for="customer_phone" value="Nomor Telepon" />
                            <x-text-input id="customer_phone" name="customer_phone" type="tel" required maxlength="255" class="mt-1.5" :value="old('customer_phone', $user->phone)" placeholder="08xxxxxxxxxx" />
                            <x-input-error :messages="$errors->get('customer_phone')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="customer_email" value="Email" />
                        <x-text-input id="customer_email" name="customer_email" type="email" required maxlength="255" class="mt-1.5" :value="old('customer_email', $user->email)" />
                        <x-input-error :messages="$errors->get('customer_email')" />
                    </div>

                    <div>
                        <x-input-label for="shipping_address" value="Alamat Pengiriman" />
                        <x-textarea id="shipping_address" name="shipping_address" rows="4" required maxlength="255" class="mt-1.5">{{ old('shipping_address', $user->address) }}</x-textarea>
                        <p class="help mt-1.5">
                            Alamat ini disalin permanen ke pesanan, terpisah dari profilmu. Ubah profilmu tidak akan mengubah pesanan lama.
                        </p>
                        <x-input-error :messages="$errors->get('shipping_address')" />
                    </div>

                    <div>
                        <x-input-label for="note" value="Catatan untuk penjual (opsional)" />
                        <x-textarea id="note" name="note" rows="2" maxlength="255" class="mt-1.5" placeholder="Contoh: titip ke satpam kalau tidak ada orang">{{ old('note') }}</x-textarea>
                        <x-input-error :messages="$errors->get('note')" />
                    </div>
                </div>
            </section>

            {{-- 2. Payment --------------------------------------------------------- --}}
            <section class="card p-6">
                <h2 class="flex items-center gap-2.5 font-display text-lg font-semibold tracking-tight text-espresso-900">
                    <span class="flex size-7 items-center justify-center rounded-full bg-espresso-800 text-xs font-semibold text-parchment-100">2</span>
                    Metode pembayaran
                </h2>

                {{-- Alpine reveals only the chosen method's instructions. Each radio is
                     a full-width card so the tap target is the whole row. --}}
                <div class="mt-5 space-y-3" x-data="{ method: @js(old('payment_method', 'cod')) }">
                    @foreach (\App\Models\Order::PAYMENT_METHODS as $method)
                        @php $label = \App\Models\Order::paymentMethodLabels()[$method]; @endphp

                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition has-[:checked]:border-cognac-400 has-[:checked]:bg-cognac-50/50 hover:border-parchment-300"
                               :class="method === '{{ $method }}' ? 'border-cognac-400 bg-cognac-50/50' : 'border-parchment-200'">
                            <input type="radio" name="payment_method" value="{{ $method }}" required
                                   x-model="method"
                                   class="mt-1 border-parchment-300 text-cognac-500 focus:ring-cognac-400 focus:ring-offset-parchment-50">

                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-espresso-900">{{ $label }}</span>

                                @if ($method === 'cod')
                                    <span class="mt-1 block text-xs text-espresso-800/55">
                                        Tidak perlu transfer sekarang. Bayar saat barang sampai.
                                    </span>
                                @else
                                    <span class="mt-2.5 block space-y-1 rounded-xl bg-parchment-50 p-3 text-xs text-espresso-800/70">
                                        @forelse ($setting->detailsFor($method) as $detail)
                                            <span class="flex justify-between gap-3">
                                                <span>{{ $detail['label'] }}</span>
                                                <strong class="text-espresso-900">{{ $detail['value'] }}</strong>
                                            </span>
                                        @empty
                                            <span class="block text-warning-700">
                                                Detail akun belum diisi admin. Kamu tetap bisa membuat pesanan dan membayar lewat simulasi.
                                            </span>
                                        @endforelse

                                        @if ($method === 'qris' && $setting->qrisImageUrl())
                                            <img src="{{ $setting->qrisImageUrl() }}" alt="Kode QRIS" class="mt-2 w-40 rounded-lg border border-parchment-200 bg-white">
                                        @endif
                                    </span>
                                @endif
                            </span>
                        </label>
                    @endforeach

                    <x-input-error :messages="$errors->get('payment_method')" />

                    <p class="help mt-1 flex items-start gap-2">
                        <x-icon name="alert" class="mt-0.5 shrink-0 text-cognac-600" />
                        Simulasi. Tidak ada uang yang benar-benar berpindah dan tidak ada payment gateway yang dihubungi.
                    </p>
                </div>
            </section>
        </div>

        {{-- 3. Submit --------------------------------------------------------- --}}
        <div class="lg:sticky lg:top-24">
            <div class="card p-6">
                <h2 class="font-display text-lg font-semibold tracking-tight text-espresso-900">Ringkasan pesanan</h2>

                <ul class="mt-5 space-y-3 text-sm">
                    @foreach ($rows as $row)
                        <li class="flex items-start justify-between gap-3">
                            <span class="min-w-0 text-espresso-800/70">
                                <span class="line-clamp-2">{{ $row['product']->name }}</span>
                                <span class="text-espresso-800/45">&times; {{ $row['quantity'] }}</span>
                            </span>
                            <span class="whitespace-nowrap font-medium text-espresso-900">
                                Rp {{ number_format($row['subtotal'], 0, ',', '.') }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-5 border-t border-parchment-200 pt-4">
                    <div class="flex justify-between">
                        <dt class="text-sm text-espresso-800/60">Ongkir</dt>
                        <dd class="text-sm text-espresso-800/55">Ditentukan penjual</dd>
                    </div>
                    <div class="mt-3 flex items-baseline justify-between border-t border-parchment-200 pt-3">
                        <dt class="font-semibold text-espresso-900">Total</dt>
                        <dd class="font-display text-2xl font-semibold text-espresso-900">
                            Rp {{ number_format($total, 0, ',', '.') }}
                        </dd>
                    </div>
                </dl>

                <x-primary-button class="mt-6 w-full">
                    Buat Pesanan <x-icon name="arrow-right" />
                </x-primary-button>

                <p class="help mt-4 text-center">
                    Stok langsung dipesan saat pesanan dibuat, bukan saat pembayaran.
                </p>
            </div>
        </div>
    </form>
</x-app-layout>