@props([
    // Tên file (không đuôi) trong public/images/flowers, vd. "anh-hoa-dang-nhap" → anh-hoa-dang-nhap.webp|jpg|jpeg|png.
    'name',
    'alt',
])

@php
    $file = collect(['webp', 'jpg', 'jpeg', 'png'])
        ->map(fn (string $extension): string => "images/flowers/{$name}.{$extension}")
        ->first(fn (string $path): bool => file_exists(public_path($path)));
@endphp

{{-- Khung ảnh vòm (bo tròn phía trên) như thiết kế đăng nhập/đăng ký. --}}
<div {{ $attributes->class('aspect-[520/480] overflow-hidden rounded-t-full rounded-b-sm bg-lh-sage-100') }}>
    @if ($file)
        <img src="{{ asset($file) }}" alt="{{ $alt }}" class="size-full object-cover" fetchpriority="high">
    @else
        {{-- Chưa có ảnh: khung giữ chỗ. Thả ảnh vào public/images/flowers/{{ $name }}.webp để hiển thị. --}}
        <div class="grid size-full place-items-center text-lh-sage-200" role="img" aria-label="{{ $alt }}">
            <x-brand.flower-mark class="h-24 w-auto" />
        </div>
    @endif
</div>
