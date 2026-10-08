@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-success-100 bg-success-50 px-4 py-3 text-sm text-success-700']) }}>
        {{ $status }}
    </div>
@endif
