@props([
    // URL ảnh đã tải lên (vd. $category->cover_url); null thì dùng ảnh tĩnh $name / khung giữ chỗ.
    'url' => null,
    // Tên file ảnh tĩnh trong public/images/flowers (không đuôi).
    'name',
    'alt' => '',
])

{{-- Ảnh chính bộ sưu tập: ưu tiên ảnh admin tải lên, chưa có thì giống x-flower-image. --}}
@if ($url)
    <div {{ $attributes->class('overflow-hidden bg-lh-sage-100') }}>
        <img src="{{ $url }}" alt="{{ $alt }}" class="size-full object-cover" loading="lazy">
    </div>
@else
    <x-flower-image :name="$name" :alt="$alt" {{ $attributes }} />
@endif
