{{--
    Single navigation for the whole app. The mobile drawer is not optional:
    the previous version had no hamburger at all, so on a phone a signed-in
    customer had no route to Katalog, Pesanan Saya or Admin.
--}}
@php
    $cartCount = array_sum(session('cart', []));
    // $isAdmin / $brandUrl come from AppLayout — one computation for the shell.
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-parchment-200 bg-parchment-50/80 backdrop-blur">
    <div class="page-container">
        <div class="flex h-18 items-center justify-between gap-4 py-4">
            <div class="flex items-center gap-3 lg:gap-10">
                <a href="{{ $brandUrl }}" class="flex items-center gap-2.5">
                    <span class="flex size-9 items-center justify-center rounded-xl bg-espresso-800 text-parchment-100">
                        <x-icon name="bag" />
                    </span>
                    <span class="font-display text-lg font-semibold tracking-tight text-espresso-900">
                        {{ config('app.name') }}
                    </span>
                </a>

                <div class="hidden items-center gap-7 text-sm lg:flex">
                    {{-- The catalog is public, so it shows for guests too.
                         Only admins are cut off: those routes 403 for them. --}}
                    @unless ($isAdmin)
                        <x-nav-link :href="route('products.catalog')" :active="request()->routeIs('products.*')">
                            Katalog
                        </x-nav-link>
                    @endunless

                    @auth
                        @unless ($isAdmin)
                            <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                                Pesanan Saya
                            </x-nav-link>
                        @endunless

                        {{-- Admins only: the link itself 403s for a customer. --}}
                        @if ($isAdmin)
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                Panel Admin
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    @unless ($isAdmin)
                        <a href="{{ route('cart.index') }}"
                           class="btn btn-ghost btn-sm relative gap-2" aria-label="Keranjang">
                            <x-icon name="cart" />
                            <span class="hidden sm:inline">Keranjang</span>
                            @if ($cartCount > 0)
                                <span class="inline-flex size-5 items-center justify-center rounded-full bg-cognac-500 text-[11px] font-semibold text-white">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                    @endunless

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="btn btn-ghost btn-sm gap-2">
                                <span class="flex size-6 items-center justify-center rounded-full bg-parchment-200 text-[11px] font-semibold text-espresso-700">
                                    {{ \Illuminate\Support\Str::substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="border-b border-parchment-200 px-4 py-3">
                                <p class="truncate text-sm font-semibold text-espresso-900">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-espresso-800/55">{{ auth()->user()->email }}</p>
                            </div>

                            <x-dropdown-link :href="$isAdmin ? route('admin.settings.account.edit') : route('profile.edit')">
                                {{ $isAdmin ? 'Akun Admin' : 'Profil Saya' }}
                            </x-dropdown-link>

                            @unless ($isAdmin)
                                <x-dropdown-link :href="route('orders.index')">Pesanan Saya</x-dropdown-link>
                            @endunless

                            @if ($isAdmin)
                                <x-dropdown-link :href="route('admin.dashboard')">Panel Admin</x-dropdown-link>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    Keluar
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                @endauth

                <button type="button"
                        class="btn btn-ghost btn-sm -ms-1 lg:hidden"
                        @click="open = ! open"
                        :aria-expanded="open"
                        aria-controls="mobile-nav"
                        aria-label="Buka menu navigasi">
                    <x-icon name="menu" class="size-6" x-show="! open" />
                    <x-icon name="x" class="size-6" x-show="open" x-cloak />
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile drawer. Same links as the desktop bar, so nothing is reachable --}}
    {{-- on desktop only. --}}
    <div id="mobile-nav" x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.outside="open = false"
         class="border-t border-parchment-200 bg-parchment-50 lg:hidden">
        <div class="page-container space-y-1 py-4 text-sm">
            @unless ($isAdmin)
                <a href="{{ route('products.catalog') }}"
                   @click="open = false"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('products.*') ? 'bg-parchment-200/70 font-semibold text-espresso-900' : 'text-espresso-700 hover:bg-parchment-100' }}">
                    <x-icon name="bag" /> Katalog
                </a>
            @endunless

            @auth
                @unless ($isAdmin)
                    <a href="{{ route('orders.index') }}"
                       @click="open = false"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('orders.*') ? 'bg-parchment-200/70 font-semibold text-espresso-900' : 'text-espresso-700 hover:bg-parchment-100' }}">
                        <x-icon name="package" /> Pesanan Saya
                    </a>

                    <a href="{{ route('profile.edit') }}"
                       @click="open = false"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('profile.*') ? 'bg-parchment-200/70 font-semibold text-espresso-900' : 'text-espresso-700 hover:bg-parchment-100' }}">
                        <x-icon name="user" /> Profil Saya
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}"
                       @click="open = false"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'bg-parchment-200/70 font-semibold text-espresso-900' : 'text-espresso-700 hover:bg-parchment-100' }}">
                        <x-icon name="sliders" /> Panel Admin
                    </a>

                    <a href="{{ route('admin.settings.account.edit') }}"
                       @click="open = false"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 {{ request()->routeIs('admin.settings.account.*') ? 'bg-parchment-200/70 font-semibold text-espresso-900' : 'text-espresso-700 hover:bg-parchment-100' }}">
                        <x-icon name="user" /> Akun Admin
                    </a>
                @endunless
            @endauth
        </div>
    </div>
</nav>
