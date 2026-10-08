@props(['label' => null, 'hint' => null])

{{-- `only()` takes a single array argument — it does NOT spread varargs, so
     passing five names used to keep only `name`. Both `value` and `checked`
     were silently dropped: the box loaded unchecked and checking it submitted
     the browser default `on`, which `boolean` rejects. --}}
<label {{ $attributes->merge(['class' => 'inline-flex items-start gap-2.5 text-sm text-espresso-700']) }}>
    <input type="checkbox" {{ $attributes->only(['name', 'value', 'checked', 'required', 'disabled'])->merge(['class' => 'mt-0.5 rounded border-parchment-300 text-cognac-500 focus:ring-cognac-400 focus:ring-offset-parchment-50']) }}>

    <span>
        {{ $label ?? $slot }}
        @if ($hint)
            <span class="block text-xs text-espresso-800/55">{{ $hint }}</span>
        @endif
    </span>
</label>
