@props([
    'quote',
    'name',
    // Dịp gửi hoa, hiện dưới tên khách.
    'occasion',
    'rating' => 5,
])

@php
    // "Đoan Trang" → "ĐT": chữ cái đầu của hai từ cuối.
    $words = array_slice(preg_split('/\s+/u', trim($name)) ?: [$name], -2);
    $initials = Str::upper(implode('', array_map(fn (string $word): string => Str::substr($word, 0, 1), $words)));
@endphp

{{-- Một lời khách gửi lại (khối "Niềm vui nở từ những điều nhỏ" ở Trang chủ). --}}
<figure {{ $attributes->class('flex flex-col rounded-sm border border-lh-border bg-lh-surface p-6 sm:p-8') }}>
    <p class="flex gap-1 text-lh-primary-dark" role="img" aria-label="{{ __('home.testimonials.stars_label', ['count' => $rating]) }}">
        @for ($star = 0; $star < $rating; $star++)
            <x-lucide-star class="size-3.5 fill-current" aria-hidden="true" />
        @endfor
    </p>

    <blockquote class="mt-6 flex-1 font-serif text-xl leading-relaxed text-lh-heading">“{{ $quote }}”</blockquote>

    <figcaption class="mt-6 flex items-center gap-3">
        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-lh-sage-100 text-xs text-lh-heading" aria-hidden="true">{{ $initials }}</span>
        <span class="min-w-0">
            <span class="block text-sm font-semibold text-lh-heading">{{ $name }}</span>
            <span class="mt-1 block text-xs text-lh-muted">{{ $occasion }}</span>
        </span>
    </figcaption>
</figure>
