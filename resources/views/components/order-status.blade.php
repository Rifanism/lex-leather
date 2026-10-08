@props(['status'])

{{--
    The one and only order status pill. The label comes from
    Order::statusLabels() so it can never drift from $order->statusLabel().

    Tones are complete class strings on purpose: Tailwind's scanner cannot see
    an interpolated "badge-{$tone}".
--}}

@php
    $tones = [
        'pending' => 'badge-warning',
        'paid' => 'badge-success',
        'shipped' => 'badge-info',
        'completed' => 'badge-neutral',
        'cancelled' => 'badge-danger',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'badge '.($tones[$status] ?? 'badge-neutral')]) }}>
    {{ \App\Models\Order::statusLabels()[$status] ?? $status }}
</span>
