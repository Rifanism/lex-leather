@props(['name', 'stroke' => 1.5])

{{--
    Inline 24x24 stroke icons, hand-authored so the app ships zero icon
    dependencies and works fully offline. Geometry is deliberately simple:
    these read fine at 16-32px, which is all this storefront needs.

    Class names are never interpolated (Tailwind's scanner would not see them),
    and the wrapper is the only place `currentColor` is applied.
--}}

@php
    $body = match ($name) {
        'bag' => '<path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'wallet' => '<path d="M3 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2"/><path d="M3 8v10a2 2 0 0 0 2 2h14V8"/><path d="M21 8v6h-4a3 3 0 0 1 0-6h4Z"/>',
        'cart' => '<path d="M3 4h2l2.5 10.5a2 2 0 0 0 2 1.5h7a2 2 0 0 0 2-1.6L20 8H6"/><circle cx="10" cy="20" r="1.25"/><circle cx="17" cy="20" r="1.25"/>',
        'search' => '<circle cx="11" cy="11" r="6"/><path d="m20 20-3.5-3.5"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'chevron-left' => '<path d="m15 6-6 6 6 6"/>',
        'check' => '<path d="m5 12 5 5 9-11"/>',
        'x' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'trash' => '<path d="M4 7h16M10 7V5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2M6 7l1 13h10l1-13M10 11v6M14 11v6"/>',
        'truck' => '<path d="M3 6h11v10H3zM14 9h4l3 3v4h-7z"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/>',
        'shield' => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/><path d="m9 12 2 2 4-4"/>',
        'package' => '<path d="M4 7.5 12 4l8 3.5v9L12 20l-8-3.5v-9Z"/><path d="M4 7.5 12 11l8-3.5M12 11v9"/>',
        'card' => '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M3 10h18"/>',
        'qr' => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><path d="M13.5 13.5h3v3h-3zM18 18h3v3h-3zM13.5 20.5h2M20.5 13.5h.5"/>',
        'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'edit' => '<path d="M4 20h4L20 8a2.5 2.5 0 0 0-3.5-3.5L4.5 16.5 4 20Z"/><path d="m15 5.5 3.5 3.5"/>',
        'sliders' => '<path d="M4 7h3M11 7h9M4 12h9M17 12h3M4 17h1M9 17h11"/><circle cx="9" cy="7" r="2.2"/><circle cx="15" cy="12" r="2.2"/><circle cx="7" cy="17" r="2.2"/>',
        'alert' => '<path d="M12 4 2.5 20h19L12 4Z"/><path d="M12 10v4M12 17h.01"/>',
        'spinner' => '<path d="M12 3a9 9 0 1 0 9 9"/>',
        // Icon names are hardcoded in source, so an unknown one is a typo, not
        // a runtime condition. Fail loudly instead of silently drawing a plus.
        default => throw new \InvalidArgumentException("Unknown x-icon [{$name}]."),
    };
@endphp

<svg {{ $attributes->merge(['class' => 'size-5 shrink-0']) }}
     viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round"
     aria-hidden="true" focusable="false">
    {!! $body !!}
</svg>
