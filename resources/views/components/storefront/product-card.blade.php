@props([
    'name',
    // Dòng phụ dưới tên: thành phần hoa, hoặc tên danh mục khi chưa có thành phần.
    'subtitle' => null,
    'price',
    // Ảnh đã upload (storage); chưa có thì dùng ảnh tĩnh public/images/flowers/{imageName} hoặc khung giữ chỗ.
    'imageUrl' => null,
    'imageName' => null,
    'badge' => null,
])

@php
    $iconButton = 'grid size-9 place-items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
@endphp

{{-- Thẻ sản phẩm storefront: ảnh + nhãn góc trái + nút tim, tên (Lora), dòng phụ, giá, nút "+". --}}
<article {{ $attributes->class('group flex flex-col') }}>
    <div class="relative">
        {{-- TODO: trỏ tới trang chi tiết sản phẩm khi có. --}}
        <a href="#" class="block rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lh-primary" aria-label="{{ $name }}">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}" alt="" loading="lazy"
                    class="aspect-[4/5] w-full rounded-sm bg-lh-sage-100 object-cover transition-opacity group-hover:opacity-95">
            @else
                <x-flower-image :name="(string) $imageName" class="aspect-[4/5] rounded-sm transition-opacity group-hover:opacity-95" />
            @endif
        </a>

        @if ($badge)
            <span class="absolute top-3 left-3 rounded-sm bg-lh-surface px-2 py-1 text-[10px] font-medium tracking-wide text-lh-heading uppercase">
                {{ $badge }}
            </span>
        @endif

        {{-- TODO: danh sách yêu thích (docs/design-vs-db-gaps.md #12). --}}
        <button type="button" class="{{ $iconButton }} absolute top-3 right-3 size-8 bg-lh-surface text-lh-heading hover:text-lh-primary"
            aria-label="{{ __('storefront.product.favorite', ['name' => $name]) }}">
            <x-lucide-heart class="size-4 stroke-[1.5]" aria-hidden="true" />
        </button>
    </div>

    <h3 class="mt-4 font-serif text-xl leading-snug">{{ $name }}</h3>
    @if ($subtitle)
        <p class="mt-1 text-xs text-lh-muted">{{ $subtitle }}</p>
    @endif

    <div class="mt-4 flex items-center justify-between gap-3">
        <x-money :amount="$price" class="text-sm font-medium tabular-nums" />
        {{-- TODO: thêm vào giỏ khi có giỏ hàng. --}}
        <button type="button" class="{{ $iconButton }} border border-lh-border text-lh-heading hover:bg-lh-sage-50"
            aria-label="{{ __('storefront.product.add_to_cart', ['name' => $name]) }}">
            <x-lucide-plus class="size-4 stroke-[1.5]" aria-hidden="true" />
        </button>
    </div>
</article>
