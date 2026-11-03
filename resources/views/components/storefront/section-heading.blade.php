@props([
    'eyebrow',
    'title',
    'id' => null,
    'description' => null,
])

{{-- Đầu mỗi khối storefront: nhãn nhỏ in hoa + tiêu đề Lora; slot $aside nằm bên phải (link "Xem tất cả", ghi chú). --}}
<div {{ $attributes->class('flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="min-w-0">
        <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ $eyebrow }}</p>
        <h2 @if ($id) id="{{ $id }}" @endif class="mt-3 font-serif text-3xl leading-tight text-lh-heading sm:text-[40px]">{{ $title }}</h2>
        @if ($description)
            <p class="mt-3 text-sm text-lh-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($aside)
        <div class="shrink-0 text-sm text-lh-muted">{{ $aside }}</div>
    @endisset
</div>
