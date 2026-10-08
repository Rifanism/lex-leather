@if ($paginator->hasPages())
    {{-- Restyled onto the project's own primitives. Laravel's markup is utility-only
         and version-specific, so editing the published view beats a CSS override. --}}
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col-reverse items-center gap-5 sm:flex-row sm:justify-between">
        <p class="text-sm text-espresso-800/60">
            Menampilkan
            <span class="font-medium text-espresso-900">{{ $paginator->firstItem() ?? 0 }}</span>
            sampai
            <span class="font-medium text-espresso-900">{{ $paginator->lastItem() ?? 0 }}</span>
            dari
            <span class="font-medium text-espresso-900">{{ $paginator->total() }}</span>
            produk
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="btn btn-outline btn-sm pointer-events-none opacity-40" aria-disabled="true">
                    <x-icon name="chevron-left" /> Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">
                    <x-icon name="chevron-left" /> Sebelumnya
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="flex size-9 items-center justify-center text-sm text-espresso-800/45">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex size-9 items-center justify-center rounded-full bg-espresso-800 text-sm font-semibold text-parchment-100">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex size-9 items-center justify-center rounded-full text-sm font-medium text-espresso-700 transition hover:bg-parchment-200 hover:text-espresso-900" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">
                    Berikutnya <x-icon name="chevron-right" />
                </a>
            @else
                <span class="btn btn-outline btn-sm pointer-events-none opacity-40" aria-disabled="true">
                    Berikutnya <x-icon name="chevron-right" />
                </span>
            @endif
        </div>
    </nav>
@endif