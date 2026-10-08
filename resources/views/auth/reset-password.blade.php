<x-app-layout variant="centered" title="Password Baru">
    <h1 class="font-display text-2xl font-semibold tracking-tight text-espresso-900">Password baru</h1>
    <p class="mt-1.5 text-sm text-espresso-800/60">Pilih password yang belum pernah kamu pakai di sini.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Password baru" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Ulangi password baru" />
            <x-text-input id="password_confirmation" class="mt-1.5" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">Simpan password</x-primary-button>
    </form>
</x-app-layout>
