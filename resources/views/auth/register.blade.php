<x-app-layout variant="centered" title="Daftar">
    <h1 class="font-display text-2xl font-semibold tracking-tight text-espresso-900">Buat akun</h1>
    <p class="mt-1.5 text-sm text-espresso-800/60">Cukup satu menit, lalu checkout jadi lebih cepat.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" class="mt-1.5" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="phone" value="Nomor HP" />
            <x-text-input id="phone" class="mt-1.5" type="text" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="08xxxxxxxxxx" />
            <p class="help mt-1.5">Dipakai kurir kalau ada yang perlu dikonfirmasi.</p>
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="address" value="Alamat" />
            <x-textarea id="address" name="address" rows="3" required autocomplete="street-address" class="mt-1.5">{{ old('address') }}</x-textarea>
            <x-input-error :messages="$errors->get('address')" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Ulangi password" />
            <x-text-input id="password_confirmation" class="mt-1.5" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">Daftar</x-primary-button>
    </form>

    <p class="mt-6 border-t border-parchment-200 pt-5 text-center text-sm text-espresso-800/60">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-cognac-600 hover:text-cognac-700 hover:underline">Masuk</a>
    </p>
</x-app-layout>