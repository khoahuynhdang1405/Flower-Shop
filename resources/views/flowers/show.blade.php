@php
    /*
     * TODO: dữ liệu mẫu theo docs/designs/storefront/04-chi-tiet-san-pham.png — chỉ dựng giao diện.
     * Khi nối DB: sản phẩm theo {slug}, size = product_sizes (theo giá, is_default chọn sẵn), ảnh = product_images.
     * Kích thước cỡ Nhỏ / Lớn là số mẫu (thiết kế chỉ ghi cỡ Vừa); nhãn nhỏ phía trên tên chưa có cột.
     * Phí giao "từ 25.000" chưa có bảng khu vực (docs/design-vs-db-gaps.md #7).
     */
    $product = [
        'name' => 'Lời thì thầm',
        'slug' => 'loi-thi-tham',
        'eyebrow' => 'Bó hoa tươi / Dịu dàng & tinh tế',
        'description' => 'Một chút hồng đào, một chút kem ấm. Hoa hồng vườn, cúc nhỏ và lá bạch đàn cùng nhau kể một lời thương thật khẽ — dành cho người bạn luôn nghĩ đến.',
        'sizes' => [
            ['id' => 'small', 'name' => 'Nhỏ', 'price' => 480000, 'dimension' => 'cao khoảng 32 cm · đường kính 24 cm'],
            ['id' => 'medium', 'name' => 'Vừa', 'price' => 680000, 'dimension' => 'cao khoảng 40 cm · đường kính 30 cm'],
            ['id' => 'large', 'name' => 'Lớn', 'price' => 880000, 'dimension' => 'cao khoảng 48 cm · đường kính 36 cm'],
        ],
        'default_size' => 'medium',
        'gallery' => [
            ['image' => 'loi-thi-tham'],
            ['image' => 'loi-thi-tham-2'],
            ['image' => 'loi-thi-tham-3'],
            ['image' => 'loi-thi-tham-4', 'video_duration' => '00:18'],
        ],
    ];
    $defaultSize = collect($product['sizes'])->firstWhere('id', $product['default_size']);
    $deliveryFeeFrom = 25000;

    $button = 'inline-flex h-12 items-center justify-center gap-3 rounded px-6 text-[15px] font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $stepButton = 'grid size-11 place-items-center text-lh-heading transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-not-allowed disabled:text-lh-border disabled:hover:bg-transparent';
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20';
@endphp

<x-layouts.storefront :title="$product['name']">
    <div class="{{ $container }} pt-5 pb-16 lg:pb-20">
        <x-storefront.breadcrumb :items="[
            [__('storefront.menu.home'), url('/')],
            [__('flowers.title'), route('flowers.index')],
            [$product['name'], null],
        ]" />

        <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-16 xl:gap-[68px]" data-product>
            <x-storefront.product-gallery :product-name="$product['name']" :items="$product['gallery']" />

            <div class="lg:pt-2">
                <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ $product['eyebrow'] }}</p>
                <h1 class="mt-5 font-serif text-[40px] leading-tight sm:text-5xl">{{ $product['name'] }}</h1>
                <p class="mt-5 font-serif text-3xl tabular-nums" aria-live="polite" data-size-price>
                    <x-money :amount="$defaultSize['price']" />
                </p>
                <p class="mt-6 border-b border-lh-border pb-7 text-[15px] leading-relaxed text-lh-muted">{{ $product['description'] }}</p>

                <x-storefront.size-picker class="mt-7" :sizes="$product['sizes']" :selected="$product['default_size']" />

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                    <div class="inline-flex items-center rounded-sm border border-lh-border bg-lh-surface" data-quantity>
                        <button type="button" class="{{ $stepButton }}" data-quantity-step="-1" aria-label="{{ __('product.quantity.decrease') }}" disabled>
                            <x-lucide-minus class="size-4 stroke-[1.5]" aria-hidden="true" />
                        </button>
                        <label for="quantity" class="sr-only">{{ __('product.quantity.label') }}</label>
                        <input id="quantity" name="quantity" type="number" inputmode="numeric" min="1" max="99" value="1" data-quantity-input
                            class="h-11 w-10 [appearance:textfield] bg-transparent text-center text-sm tabular-nums focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-lh-primary [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                        <button type="button" class="{{ $stepButton }}" data-quantity-step="1" aria-label="{{ __('product.quantity.increase') }}">
                            <x-lucide-plus class="size-4 stroke-[1.5]" aria-hidden="true" />
                        </button>
                    </div>

                    <p class="flex items-center gap-2 text-[13px]">
                        <span class="size-2 rounded-full bg-lh-primary" aria-hidden="true"></span>
                        {{ __('product.availability.in_stock') }}
                    </p>
                </div>

                {{-- TODO: thêm vào giỏ / mua ngay khi có giỏ hàng. --}}
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <button type="button" class="{{ $button }} border border-lh-primary bg-lh-surface text-lh-heading hover:bg-lh-sage-50">
                        {{ __('product.actions.add_to_cart') }}
                        <x-lucide-shopping-bag class="size-5 stroke-[1.5]" aria-hidden="true" />
                    </button>
                    <button type="button" class="{{ $button }} bg-lh-primary text-white hover:bg-lh-primary-dark">
                        {{ __('product.actions.buy_now') }}
                        <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                    </button>
                </div>

                <div class="mt-6 flex items-start gap-4 rounded-sm bg-lh-sage-50 px-5 py-4">
                    <x-lucide-leaf class="mt-0.5 size-5 shrink-0 stroke-[1.5] text-lh-primary" aria-hidden="true" />
                    <div>
                        <p class="text-sm font-medium">{{ __('product.freshness.title') }}</p>
                        <p class="mt-1 text-xs leading-relaxed text-lh-muted">{{ __('product.freshness.body') }}</p>
                    </div>
                </div>

                <p class="mt-7 text-xs leading-relaxed text-lh-muted">
                    {{ __('product.delivery.fee', ['fee' => number_format($deliveryFeeFrom, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency')]) }}<br>
                    {{ __('product.delivery.lead_time') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts.storefront>
