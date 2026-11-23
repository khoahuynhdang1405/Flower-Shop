@props(['label', 'value', 'note' => null, 'icon' => null])

{{-- Thẻ số liệu đầu trang admin. Số < 10 hiển thị 2 chữ số như thiết kế ("06"). $icon: tên component Lucide góc phải (Tổng quan). --}}
<div {{ $attributes->class('rounded-md border border-lh-border bg-lh-surface px-4 py-4 sm:px-6 sm:py-6') }}>
    <div class="flex items-start justify-between gap-2 sm:gap-4">
        <p class="text-[13px] text-lh-muted sm:text-[15px]">{{ $label }}</p>
        @if ($icon)
            <x-dynamic-component :component="$icon" class="size-5 shrink-0 stroke-[1.5] text-lh-heading sm:size-6" aria-hidden="true" />
        @endif
    </div>
    {{-- Số tiền ("12.450.000 VNĐ") dài hơn số đếm nên dùng cỡ chữ nhỏ hơn để nằm trên một dòng. --}}
    <p @class([
        'mt-3 leading-none font-semibold text-lh-primary-dark tabular-nums',
        'text-3xl sm:text-4xl' => is_int($value),
        'text-lg sm:text-2xl 2xl:text-[28px]' => ! is_int($value),
    ])>
        {{ is_int($value) ? str_pad((string) $value, 2, '0', STR_PAD_LEFT) : $value }}
    </p>
    @if ($note)
        <p class="mt-3 text-xs leading-snug sm:mt-4 sm:text-[13px]">{{ $note }}</p>
    @endif
</div>
