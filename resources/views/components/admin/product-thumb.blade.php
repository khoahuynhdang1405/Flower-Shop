@props(['product'])

{{-- Ảnh vuông nhỏ của sản phẩm trong bảng admin: ảnh chính đã upload, chưa có thì ảnh tĩnh / khung giữ chỗ. --}}
@if ($product->primaryImage)
    <img src="{{ $product->primaryImage->url }}" alt="" loading="lazy" {{ $attributes->class('size-[58px] shrink-0 rounded object-cover') }}>
@else
    <x-flower-image :name="$product->slug" {{ $attributes->class('size-[58px] shrink-0 rounded') }} />
@endif
