<section>
    <header class="border-b border-parchment-200 pb-5">
        <h2 class="font-display text-xl font-semibold tracking-tight text-espresso-900">Informasi Profil</h2>
        <p class="mt-2 text-sm text-espresso-800/60">Nama, email, dan alamat yang dipakai untuk pengiriman.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-warning-100 bg-warning-50 px-4 py-3 text-sm text-warning-700">
                    Emailmu belum diverifikasi.

                    <button form="send-verification" class="font-semibold underline hover:no-underline">Kirim ulang verifikasi.</button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1.5 font-medium">Tautan baru sudah dikirim. Cek juga folder spam.</p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" value="Nomor HP" />
            <x-text-input id="phone" name="phone" type="text" :value="old('phone', $user->phone)" required autocomplete="tel" placeholder="08xxxxxxxxxx" />
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="address" value="Alamat" />
            <x-textarea id="address" name="address" rows="3" required autocomplete="street-address">{{ old('address', $user->address) }}</x-textarea>
            <x-input-error :messages="$errors->get('address')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button><x-icon name="check" /> Simpan</x-primary-button>

            @if (session('status') === 'profile-updated')
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