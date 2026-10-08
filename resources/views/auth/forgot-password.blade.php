<x-app-layout variant="centered" title="Lupa Password">
    <h1 class="font-display text-2xl font-semibold tracking-tight text-espresso-900">Lupa password?</h1>
    <p class="mt-1.5 text-sm text-espresso-800/60">
        Kasih email yang kamu pakai daftar. Kami kirim tautan untuk buat password baru.
    </p>

    <x-auth-session-status class="mb-5 mt-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-full">Kirim tautan reset</x-primary-button>
    </form>

    <p class="mt-6 border-t border-parchment-200 pt-5 text-center text-sm">
        <a href="{{ route('login') }}" class="font-semibold text-cognac-600 hover:text-cognac-700 hover:underline">Kembali ke halaman masuk</a>
    </p>
</x-app-layout>
