<section>
    <header class="border-b border-parchment-200 pb-5">
        <h2 class="font-display text-xl font-semibold tracking-tight text-espresso-900">Ganti Password</h2>
        <p class="mt-2 text-sm text-espresso-800/60">Pakai password panjang dan acak. Jangan reuse password lain.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Password sekarang" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Password baru" />
            <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Ulangi password baru" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button><x-icon name="check" /> Simpan</x-primary-button>

            @if (session('status') === 'password-updated')
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