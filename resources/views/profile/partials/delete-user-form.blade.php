<section>
    @php($hasOrders = $user->orders()->exists())

    <header class="border-b border-parchment-200 pb-5">
        <h2 class="font-display text-xl font-semibold tracking-tight text-espresso-900">Hapus Akun</h2>
        <p class="mt-2 text-sm leading-relaxed text-espresso-800/60">
            @if ($hasOrders)
                Akunmu punya riwayat pesanan, jadi tombolnya dinonaktifkan. Pesanan adalah
                bukti transaksi — datanya ikut terhapus kalau akun dihapus, dan itu tidak bisa dibatalkan.
            @else
                Menghapus akun akan menghilangkan data profilmu secara permanen dan tidak bisa dibatalkan.
            @endif
        </p>
    </header>

    {{-- `false`/`null` attributes are dropped when the bag renders, so the
         bindings vanish entirely for a user who has never ordered. --}}
    <x-danger-button class="mt-5"
                     :disabled="$hasOrders"
                     :title="$hasOrders ? 'Tidak bisa dihapus selama masih ada pesanan' : null"
                     x-data=""
                     x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        <x-icon name="trash" /> Hapus Akun
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <h2 class="font-display text-lg font-semibold text-espresso-900">Yakin mau hapus akun?</h2>

            <p class="mt-2 text-sm leading-relaxed text-espresso-800/60">
                Tindakan ini tidak bisa dibatalkan. Masukkan password untuk konfirmasi.
            </p>

            <div class="mt-5">
                <x-input-label for="password" value="Password" class="sr-only" />

                <x-text-input id="password" name="password" type="password" placeholder="Password" />

                <x-input-error :messages="$errors->userDeletion->get('password')" />
                <x-input-error :messages="$errors->userDeletion->get('has_orders')" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-danger-button>Hapus Akun</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>