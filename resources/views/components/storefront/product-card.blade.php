@props([
    /** @var \App\Models\Product $product kèm category, defaultSize, primaryImage */
    'product',
    'badge' => null,
])

@php
    $isSoldOut = $product->stock < 1;
    $iconButton = 'grid size-9 place-items-center rounded-full transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
@endphp

{{-- Thẻ sản phẩm storefront: ảnh + nhãn góc trái + nút tim, tên (Lora), danh mục, giá, nút "+" thêm 1 bó cỡ mặc định vào giỏ. --}}
<article {{ $attributes->class('group flex flex-col') }}>
    <div class="relative">
        <a href="{{ route('flowers.show', $product->slug) }}" class="block rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lh-primary" aria-label="{{ $product->name }}">
            @if ($product->primaryImage)
                <img src="{{ $product->primaryImage->url }}" alt="" loading="lazy"
                    class="aspect-[4/5] w-full rounded-sm bg-lh-sage-100 object-cover transition-opacity group-hover:opacity-95">
            @else
                <x-flower-image :name="$product->slug" class="aspect-[4/5] rounded-sm transition-opacity group-hover:opacity-95" />
            @endif
        </a>

        @if ($badge)
            <span class="absolute top-3 left-3 rounded-sm bg-lh-surface px-2 py-1 text-[10px] font-medium tracking-wide text-lh-heading uppercase">
                {{ $badge }}
            </span>
        @endif

        {{-- TODO: danh sách yêu thích (docs/design-vs-db-gaps.md #12). --}}
        <button type="button" class="{{ $iconButton }} absolute top-3 right-3 size-8 bg-lh-surface text-lh-heading hover:text-lh-primary"
            aria-label="{{ __('storefront.product.favorite', ['name' => $product->name]) }}">
            <x-lucide-heart class="size-4 stroke-[1.5]" aria-hidden="true" />
        </button>
    </div>

    <h3 class="mt-4 font-serif text-xl leading-snug">{{ $product->name }}</h3>
    <p class="mt-1 text-xs text-lh-muted">{{ $product->category->name }}</p>

    <div class="mt-4 flex items-center justify-between gap-3">
        <x-money :amount="$product->display_price" class="text-sm font-medium tabular-nums" />

        @if ($isSoldOut)
            <span class="text-xs text-lh-muted">{{ __('storefront.product.sold_out') }}</span>
        @else
            {{-- Không có JavaScript: gửi form thường, về trang giỏ. Có JavaScript: resources/js/quick-add.js gửi qua /api/cart/items. --}}
            <form method="POST" action="{{ route('cart.flowers.store') }}" data-quick-add data-name="{{ $product->name }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                @if ($product->defaultSize)
                    <input type="hidden" name="size" value="{{ $product->defaultSize->id }}">
                @endif
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="{{ $iconButton }} border border-lh-border text-lh-heading hover:bg-lh-sage-50 disabled:cursor-wait group/add data-[added]:border-lh-primary data-[added]:bg-lh-primary data-[added]:text-white"
                    aria-label="{{ __('storefront.product.add_to_cart', ['name' => $product->name]) }}">
                    <x-lucide-plus class="size-4 stroke-[1.5] group-data-[added]/add:hidden" aria-hidden="true" />
                    <x-lucide-check class="hidden size-4 stroke-[1.5] group-data-[added]/add:block" aria-hidden="true" />
                </button>
            </form>
        @endif
    </div>
</article>
