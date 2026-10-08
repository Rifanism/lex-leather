<x-app-layout title="Profil">
    <x-slot name="header">
        <p class="eyebrow">Akun</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-espresso-900">Profil Saya</h1>
        <p class="mt-2 max-w-2xl text-sm text-espresso-800/60">
            Data di sini dipakai untuk semua pesanan berikutnya, jadi isi sekali saja dengan benar.
        </p>
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <div class="card p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="card border-danger-100 p-6 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>