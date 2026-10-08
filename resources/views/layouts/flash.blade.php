@php
    // One list of notices so the wrapper markup is written once. `tone` maps to a
    // complete class string — Tailwind's scanner cannot see interpolated names.
    $notices = array_filter([
        session('status') ? ['tone' => 'success', 'icon' => 'check', 'text' => session('status')] : null,
        session('error') ? ['tone' => 'danger', 'icon' => 'alert', 'text' => session('error')] : null,
        $errors->any() ? ['tone' => 'warning', 'icon' => 'alert', 'text' => null] : null,
    ]);

    $tones = [
        'success' => 'border-success-100 bg-success-50 text-success-700',
        'danger' => 'border-danger-100 bg-danger-50 text-danger-700',
        'warning' => 'border-warning-100 bg-warning-50 text-warning-700',
    ];
@endphp

@if ($notices)
    <div class="page-container pt-6">
        @foreach ($notices as $notice)
            <div class="mb-3 flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm {{ $tones[$notice['tone']] }} {{ $notice['text'] === null ? 'items-start' : 'items-center' }}">
                <x-icon :name="$notice['icon']" class="mt-0.5 shrink-0" />

                @if ($notice['text'] !== null)
                    <p>{{ $notice['text'] }}</p>
                @else
                    <ul class="list-disc space-y-1 ps-4">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
@endif