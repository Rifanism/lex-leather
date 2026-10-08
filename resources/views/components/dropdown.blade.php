@props(['align' => 'right', 'width' => '48'])

{{--
    Dropdown panel. `mt-2` keeps it clear of the sticky nav's 1px border.

    Widths must be a `match` of complete class strings: Tailwind's scanner never
    sees an interpolated `w-{{ $width }}`, so it would ship no CSS at all.
--}}
@php
    $widthClasses = match ($width) {
        '48' => 'w-48',
        '56' => 'w-56',
        '60' => 'w-60',
        '72' => 'w-72',
        default => $width,
    };
@endphp

<div x-data="{ open: false, close() { this.open = false } }" @click.outside="open = false" @keydown.escape.window="close()" class="relative">
    <div x-on:click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
         class="absolute z-50 mt-2 {{ $align === 'right' ? 'end-0' : 'start-0' }} {{ $widthClasses }} origin-top rounded-2xl border border-parchment-200 bg-white p-1.5 shadow-lift"
         style="display: none">
        {{ $content }}
    </div>
</div>