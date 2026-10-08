<x-app-layout title="Akun Admin">
    <x-slot name="header">
        <p class="eyebrow">Administrasi</p>
        <h1 class="font-display text-3xl font-semibold tracking-tight text-espresso-900">Akun Admin</h1>
        <p class="mt-2 max-w-2xl text-sm text-espresso-800/60">
            Akun untuk masuk ke panel ini. Tidak ada alamat atau nomor HP di sini —
            itu khusus pelanggan untuk pengiriman.
        </p>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <div class="card p-6 sm:p-8">
            <section>
                <header class="border-b border-parchment-200 pb-5">
                    <h2 class="font-display text-xl font-semibold tracking-tight text-espresso-900">Informasi Akun</h2>
                    <p class="mt-2 text-sm text-espresso-800/60">Nama tampil di panel dan email untuk masuk.</p>
                </header>

                <form method="POST" action="{{ route('admin.settings.account.update') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" value="Nama" />
                        <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button><x-icon name="check" /> Simpan</x-primary-button>

                        @if (session('status') === 'account-updated')
                            <p x-data="{ show: true }"
                               x-show="show"
                               x-transition
                               x-init="setTimeout(() => show = false, 2500)"
                               class="flex items-center gap-1.5 text-sm font-medium text-success-600">
                                <x-icon name="check" /> Tersimpan.
                            </p>
                        @endif
                    </div>
                </form>
            </section>
        </div>

        <div class="card p-6 sm:p-8">
            {{-- Reused verbatim from the customer profile: same route, same
                 validation bag, nothing about it is customer-specific. --}}
            @include('profile.partials.update-password-form')
        </div>
    </div>
</x-app-layout>
