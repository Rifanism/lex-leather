@php
    $title = trim(($title ?? '').' · '.config('app.name'), ' ·');

    // Gate for the back control: without a same-origin referer there is no
    // previous page to traverse to, so the button would be dead. Compared by
    // host — `url('/')` is a prefix of every absolute URL, so a starts_with
    // check could never fail.
    $referer = request()->headers->get('referer');
    $canGoBack = is_string($referer)
        && strcasecmp((string) parse_url($referer, PHP_URL_HOST), request()->getHost()) === 0;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#FBF8F3">

        <title>{{ $title }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&family=fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="min-h-screen bg-parchment-50 font-sans text-espresso-800 antialiased">
        <div class="grain-overlay" aria-hidden="true"></div>

        @if ($variant === 'centered')
            {{-- Auth / guest: no nav, no cart, just the brand and a card. --}}
            <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12">
                @if ($canGoBack)
                    <div class="absolute inset-x-0 top-0 p-4">
                        <x-back />
                    </div>
                @endif

                <a href="{{ $brandUrl }}" class="group flex flex-col items-center gap-3">
                    <span class="flex size-14 items-center justify-center rounded-2xl bg-espresso-800 text-parchment-100 shadow-lift transition group-hover:bg-espresso-900">
                        <x-icon name="bag" class="size-7" />
                    </span>
                    <span class="font-display text-xl font-semibold tracking-tight text-espresso-900">
                        {{ config('app.name') }}
                    </span>
                </a>

                <div class="mt-8 w-full max-w-md">
                    <div class="rounded-3xl border border-parchment-200 bg-white p-7 shadow-card sm:p-8">
                        <x-auth-session-status class="mb-5" :status="session('status')" />
                        {{ $slot }}
                    </div>
                </div>

                <p class="mt-8 text-xs text-espresso-800/50">
                    &copy; {{ now()->year }} {{ config('app.name') }}
                </p>
            </div>
        @else
            <div class="relative flex min-h-screen flex-col">
                @include('layouts.navigation')
                @include('layouts.flash')

                {{-- Page-level control, so it sits above the title rather than
                     after it — the same order a breadcrumb uses. --}}
                @if ($canGoBack)
                    <div class="page-container pt-6">
                        <x-back />
                    </div>
                @endif

                @isset($header)
                    <header class="border-b border-parchment-200 bg-white/70 backdrop-blur">
                        <div class="page-container py-8">{{ $header }}</div>
                    </header>
                @endisset

                <main class="page-container flex-1 py-10">{{ $slot }}</main>

                @include('layouts.footer')
            </div>
        @endif
    </body>
</html>
