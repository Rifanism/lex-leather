@props([
    'title' => '',
    'align' => 'center',
])

<div class="relative z-50" x-data="{ open: false }" x-on:keydown.window.escape="open = false" x-show="open" x-cloak>
    {{-- Backdrop. Also the click target for closing. --}}
    <div x-show="open" class="fixed inset-0 bg-espresso-950/50 backdrop-blur-sm" aria-hidden="true"></div>

    <div class="fixed inset-0 overflow-y-auto">
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-lg transform overflow-hidden rounded-3xl border border-parchment-200 bg-white p-6 shadow-lift">
                <div class="flex items-start justify-between gap-4">
                    <h2 class="font-display text-lg font-semibold text-espresso-900">{{ $title }}</h2>
                    <button type="button" class="-me-1 -mt-1 rounded-full p-1.5 text-espresso-700 transition hover:bg-parchment-100" x-on:click="open = false">
                        <x-icon name="x" />
                        <span class="sr-only">Tutup</span>
                    </button>
                </div>

                <div class="mt-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>