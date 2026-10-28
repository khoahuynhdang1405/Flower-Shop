@props([
    // success: Đang hiển thị / Đang áp dụng · warning: Sắp diễn ra · neutral: Bản nháp / Tạm dừng / Đã kết thúc.
    'tone' => 'success',
])

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center rounded-full px-3 py-1 text-[13px] leading-5 whitespace-nowrap',
    'bg-lh-sage-50 text-lh-primary' => $tone === 'success',
    'bg-lh-warning-bg text-lh-amber' => $tone === 'warning',
    'bg-lh-canvas text-lh-muted' => $tone === 'neutral',
]) }}>{{ $slot }}</span>
