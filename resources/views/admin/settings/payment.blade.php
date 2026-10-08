<x-app-layout title="Pengaturan Pembayaran">
    <x-slot name="header">
        <p class="eyebrow">Administrasi</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Pengaturan Pembayaran</h1>
        <p class="mt-2 max-w-2xl text-sm text-espresso-800/60">
            Data di bawah tampil ke pelanggan di halaman checkout. Boleh dikosongkan
            apa pun, pelanggan tetap bisa membuat pesanan dan membayar lewat simulasi.
        </p>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('admin.settings.payment.update') }}" enctype="multipart/form-data"
              class="card space-y-8 p-6 sm:p-8">
            @csrf
            @method('PATCH')

            {{-- Bank ---------------------------------------------------------------- --}}
            <fieldset class="space-y-4">
                <legend class="flex items-center gap-2 font-display text-lg font-semibold tracking-tight text-espresso-900">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-parchment-100 text-espresso-700">
                        <x-icon name="card" />
                    </span>
                    Transfer Bank
                </legend>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="bank_name" value="Nama Bank" />
                        <x-text-input id="bank_name" name="bank_name" class="mt-1.5"
                                      :value="old('bank_name', $setting->bank_name)" placeholder="BCA" />
                        <x-input-error :messages="$errors->get('bank_name')" />
                    </div>

                    <div>
                        <x-input-label for="bank_account_number" value="No. Rekening" />
                        <x-text-input id="bank_account_number" name="bank_account_number" inputmode="numeric" class="mt-1.5"
                                      :value="old('bank_account_number', $setting->bank_account_number)" placeholder="1234567890" />
                        <x-input-error :messages="$errors->get('bank_account_number')" />
                    </div>

                    <div>
                        <x-input-label for="bank_account_holder" value="Atas Nama" />
                        <x-text-input id="bank_account_holder" name="bank_account_holder" class="mt-1.5"
                                      :value="old('bank_account_holder', $setting->bank_account_holder)" placeholder="PT Lex Leather" />
                        <x-input-error :messages="$errors->get('bank_account_holder')" />
                    </div>
                </div>
            </fieldset>

            {{-- E-Wallet -------------------------------------------------------------- --}}
            <fieldset class="space-y-4 border-t border-parchment-200 pt-8">
                <legend class="flex items-center gap-2 font-display text-lg font-semibold tracking-tight text-espresso-900">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-parchment-100 text-espresso-700">
                        <x-icon name="wallet" />
                    </span>
                    E-Wallet
                </legend>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="ewallet_provider" value="Provider" />
                        <x-text-input id="ewallet_provider" name="ewallet_provider" class="mt-1.5"
                                      :value="old('ewallet_provider', $setting->ewallet_provider)" placeholder="DANA" />
                        <x-input-error :messages="$errors->get('ewallet_provider')" />
                    </div>

                    <div>
                        <x-input-label for="ewallet_number" value="Nomor" />
                        <x-text-input id="ewallet_number" name="ewallet_number" inputmode="numeric" class="mt-1.5"
                                      :value="old('ewallet_number', $setting->ewallet_number)" placeholder="081298765432" />
                        <x-input-error :messages="$errors->get('ewallet_number')" />
                    </div>

                    <div>
                        <x-input-label for="ewallet_holder" value="Atas Nama" />
                        <x-text-input id="ewallet_holder" name="ewallet_holder" class="mt-1.5"
                                      :value="old('ewallet_holder', $setting->ewallet_holder)" placeholder="PT Lex Leather" />
                        <x-input-error :messages="$errors->get('ewallet_holder')" />
                    </div>
                </div>
            </fieldset>

            {{-- QRIS ----------------------------------------------------------------- --}}
            <fieldset class="space-y-4 border-t border-parchment-200 pt-8">
                <legend class="flex items-center gap-2 font-display text-lg font-semibold tracking-tight text-espresso-900">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-parchment-100 text-espresso-700">
                        <x-icon name="qr" />
                    </span>
                    Kode QRIS
                </legend>

                <div>
                    <x-input-label for="qris_image" value="Gambar kode QRIS" />
                    <input id="qris_image" name="qris_image" type="file" accept="image/jpeg,image/png,image/webp"
                           class="field mt-1.5 file:me-3 file:rounded-full file:border-0 file:bg-parchment-200 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-espresso-800 hover:file:bg-parchment-300">
                    <p class="help mt-1.5">JPG/PNG/WEBP, maksimal 2 MB. Tampil di checkout dan di halaman pesanan.</p>
                    <x-input-error :messages="$errors->get('qris_image')" />
                </div>

                @if ($setting->qrisImageUrl())
                    <div class="flex flex-wrap items-center gap-4">
                        <img src="{{ $setting->qrisImageUrl() }}" alt="Kode QRIS saat ini" loading="lazy"
                             class="size-32 rounded-xl border border-parchment-200 bg-white object-contain">
                        <x-checkbox name="remove_qris_image" value="1" label="Hapus gambar ini" />
                    </div>
                    <x-input-error :messages="$errors->get('remove_qris_image')" />
                @endif
            </fieldset>

            <div class="flex justify-end gap-3 border-t border-parchment-200 pt-8">
                <x-primary-button><x-icon name="check" /> Simpan Pengaturan</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>