@props(['active'])

{{--
    Active state is `aria-current`, not a colour class, so the underline and the
    screen-reader announcement are the same source of truth.
--}}
<a {{ $attributes->merge([
    'class' => 'nav-underline inline-flex items-center text-sm font-medium transition',
    'aria-current' => ($active ?? false) ? 'page' : null,
]) }}>
    {{ $slot }}
</a>