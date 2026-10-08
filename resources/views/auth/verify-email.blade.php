<x-app-layout variant="centered" title="Verifikasi Email">
    <span class="flex size-12 items-center justify-center rounded-2xl bg-cognac-50 text-cognac-600">
        <x-icon name="check" class="size-6" />
    </span>

    <h1 class="mt-4 font-display text-2xl font-semibold tracking-tight text-espresso-900">Cek emailmu</h1>
    <p class="mt-1.5 text-sm text-espresso-800/60">
        Kami sudah kirim tautan verifikasi ke alamat yang kamu daftarkan. Klik tautannya untuk mengaktifkan akun.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-5 rounded-xl border border-success-100 bg-success-50 px-4 py-3 text-sm text-success-700">
            Tautan verifikasi baru sudah dikirim. Cek juga folder spam.
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <x-primary-button class="w-full">Kirim ulang email</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-secondary-button class="w-full">Keluar</x-secondary-button>
        </form>
    </div>
</x-app-layout>
