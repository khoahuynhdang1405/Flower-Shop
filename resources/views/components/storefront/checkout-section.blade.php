@props([
    'id',
    // Số thứ tự hiển thị trong vòng tròn: 1 → "01".
    'number',
    'title',
    'description' => null,
    // Khối thiệp dùng nền hồng thay cho thẻ trắng có viền.
    'blush' => false,
])

{{-- Một khối của form thanh toán: số thứ tự + tiêu đề Lora + mô tả, nội dung bên dưới. --}}
<section id="{{ $id }}" aria-labelledby="{{ $id }}-title" {{ $attributes->class([
    'scroll-mt-6 rounded-md px-5 py-6 sm:px-8 sm:py-8',
    'bg-lh-blush-card' => $blush,
    'border border-lh-border bg-lh-surface' => ! $blush,
]) }}>
    <div class="flex items-start gap-4">
        <span @class([
            'mt-1 grid size-8 shrink-0 place-items-center rounded-full text-xs text-lh-primary-dark tabular-nums',
            'bg-lh-surface' => $blush,
            'bg-lh-addon-selected' => ! $blush,
        ]) aria-hidden="true">{{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}</span>
        <div>
            <h2 id="{{ $id }}-title" class="font-serif text-[26px] leading-tight text-lh-primary-dark sm:text-[28px]">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1.5 text-[13px] text-lh-addon-muted">{{ $description }}</p>
            @endif
        </div>
    </div>

    <div class="mt-6">
        {{ $slot }}
    </div>
</section>
