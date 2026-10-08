@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm pointer-events-none opacity-40" aria-disabled="true">
                <x-icon name="chevron-left" /> Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">
                <x-icon name="chevron-left" /> Sebelumnya
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">
                Berikutnya <x-icon name="chevron-right" />
            </a>
        @else
            <span class="btn btn-outline btn-sm pointer-events-none opacity-40" aria-disabled="true">
                Berikutnya <x-icon name="chevron-right" />
            </span>
        @endif
    </nav>
@endif