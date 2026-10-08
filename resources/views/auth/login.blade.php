<x-app-layout variant="centered" title="Masuk">
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <h1 class="font-display text-2xl font-semibold tracking-tight text-espresso-900">Masuk ke akun</h1>
    <p class="mt-1.5 text-sm text-espresso-800/60">Lanjutkan-ke checkout lebih cepat dan lihat riwayat pesananmu.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <div class="flex items-baseline justify-between gap-3">
                <x-input-label for="password" value="Password" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-cognac-600 hover:text-cognac-700 hover:underline" href="{{ route('password.request') }}">Lupa password?</a>
                @endif
            </div>
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-checkbox name="remember" label="Ingat saya" />

        <x-primary-button class="w-full">Masuk</x-primary-button>
    </form>

    <p class="mt-6 border-t border-parchment-200 pt-5 text-center text-sm text-espresso-800/60">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-cognac-600 hover:text-cognac-700 hover:underline">Daftar gratis</a>
    </p>
</x-app-layout>