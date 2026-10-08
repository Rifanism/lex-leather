@php
    // Tones are complete class strings: Tailwind's scanner cannot see an
    // interpolated "badge-{$tone}".
    $tone = $product->material === 'genuine' ? 'badge-accent' : 'badge-neutral';
@endphp

<a href="{{ route('products.show', $product) }}" class="card card-hover group flex flex-col overflow-hidden">
    <div class="card-media relative aspect-[4/3]">
        <img src="{{ $product->imageUrl() }}"
             alt="{{ $product->name }}"
             loading="lazy"
             class="size-full transition duration-500 group-hover:scale-105 {{ $product->hasImage() ? 'object-cover' : 'object-contain' }}">

        @unless ($product->isInStock())
            <span class="absolute inset-x-0 bottom-0 bg-espresso-900/80 py-1.5 text-center text-xs font-medium text-parchment-100 backdrop-blur">
                Stok habis
            </span>
        @endunless
    </div>

    {{-- The badge sits on the category row, not beside the price: at
         lg:grid-cols-4 the content box is only 199px wide and price + badge
         need 203-211px, so sharing a row made both of them wrap. The eyebrow
         needs min-w-0 for truncate to actually shrink inside a flex row. --}}
    <div class="flex flex-1 flex-col p-5">
        <div class="flex items-center justify-between gap-3">
            <p class="eyebrow min-w-0 truncate">{{ $product->category?->name }}</p>

            <span class="badge {{ $tone }} shrink-0">{{ $product->materialLabel() }}</span>
        </div>

        <h3 class="mt-2 font-display text-lg font-semibold leading-snug tracking-tight text-espresso-900">
            {{ $product->name }}
        </h3>

        <p class="mt-auto pt-4 font-display text-lg font-semibold text-espresso-900">
            {{ $product->formattedPrice() }}
        </p>
    </div>
</a>