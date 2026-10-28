@props(['label', 'value', 'note' => null])

{{-- Thẻ số liệu đầu trang admin. Số < 10 hiển thị 2 chữ số như thiết kế ("06"). --}}
<div {{ $attributes->class('rounded-md border border-lh-border bg-lh-surface px-6 py-6') }}>
    <p class="text-[15px] text-lh-muted">{{ $label }}</p>
    <p class="mt-3 text-4xl leading-none font-semibold text-lh-primary-dark tabular-nums">
        {{ is_int($value) ? str_pad((string) $value, 2, '0', STR_PAD_LEFT) : $value }}
    </p>
    @if ($note)
        <p class="mt-4 text-[13px] leading-snug">{{ $note }}</p>
    @endif
</div>
