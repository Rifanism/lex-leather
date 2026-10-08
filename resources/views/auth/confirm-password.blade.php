<x-app-layout variant="centered" title="Konfirmasi Password">
    <h1 class="font-display text-2xl font-semibold tracking-tight text-espresso-900">Area aman</h1>
    <p class="mt-1.5 text-sm text-espresso-800/60">
        Masukkan password sekali lagi untuk melanjutkan. Ini melanjutkan langkah terakhir checkout.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-full">Konfirmasi</x-primary-button>
    </form>
</x-app-layout>
