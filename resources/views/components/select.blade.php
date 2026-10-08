@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'field']) }}>
    {{ $slot }}
</select>
