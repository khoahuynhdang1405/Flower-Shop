@props([
    // Tên file (không đuôi) trong public/images/flowers.
    'name',
    'alt' => '',
])

@php
    $file = collect(['webp', 'jpg', 'jpeg', 'png'])
        ->map(fn (string $extension): string => "images/flowers/{$name}.{$extension}")
        ->first(fn (string $path): bool => file_exists(public_path($path)));
@endphp

{{-- Ảnh hoa từ public/images/flowers; chưa có file thì hiện khung giữ chỗ màu sage. --}}
<div {{ $attributes->class('overflow-hidden bg-lh-sage-100') }}>
    @if ($file)
        <img src="{{ asset($file) }}" alt="{{ $alt }}" class="size-full object-cover" loading="lazy">
    @else
        <div class="grid size-full place-items-center text-lh-sage-200" @if ($alt) role="img" aria-label="{{ $alt }}" @endif>
            <x-brand.flower-mark class="h-1/2 w-auto" />
        </div>
    @endif
</div>
