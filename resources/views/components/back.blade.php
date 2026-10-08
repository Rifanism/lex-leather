{{-- Back control: conventional <a href> link.
     - When the request carries a same-origin Referer, the button goes to the
       *parent path* (the Referer URL with its last path segment removed).
       This gives the expected “one level up” behaviour the user asked for:
         /admin/orders        → /admin
         /admin/products/create → /admin/products
     - Without a same-origin Referer (fresh tab, cross-origin, or direct access)
       the button falls back to the catalog so that there is always a working link.
     - Because the link is a plain <a href> and not a script-driven
       `history.back()`, the browser’s own back key continues the normal history
       trail — there is no “undo” trap. --}}
@props(['fallback' => route('products.catalog')])
@php
$ref = request()->headers->get('referer');
$isSameOrigin = is_string($ref)
    && strcasecmp((string) parse_url($ref, PHP_URL_HOST), request()->getHost()) === 0;
$target = $isSameOrigin
    ? // strip the last path segment, keep query string / fragment if present
      (function () use ($ref) {
        $p = parse_url($ref, PHP_URL_PATH) ?? '';
        $p = rtrim($p, '/');
        if ($p === '') return $ref;              // root – fall back
        $last = strrpos($p, '/');
        if ($last === false) return $ref;        // no slash – fall back
        $parent = substr($p, 0, $last);
        $rest = '';
        if (preg_match('/(\?.*+)$/', $ref, $m)) $rest = $m[1];
        if (preg_match('/(#.*+)$/', $ref, $m)) $rest = $m[1];
        return $parent . $rest;
      })($ref)
    : $fallback;
@endphp
<a href="{{ $target }}" class="btn btn-ghost btn-sm -ms-2 gap-1.5" aria-label="Kembali">
    <x-icon name="chevron-left" class="size-4" />
    Kembali
</a>
