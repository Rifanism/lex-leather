@props(['disabled' => false, 'rows' => 3])

<textarea @disabled($disabled) rows="{{ $rows }}" {{ $attributes->merge(['class' => 'field resize-y']) }}>{{ $slot }}</textarea>
