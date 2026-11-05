@php
    /**
     * @var \App\Models\Product $product  đã nạp category, sizes (theo giá), images (theo sort_order)
     *
     * Kích thước dùng chung products.dimension_info cho mọi size (product_sizes không có cột kích thước).
     * Gallery hiện toàn bộ ảnh, không lọc theo size; chưa có ảnh upload thì dùng ảnh tĩnh theo slug / khung giữ chỗ.
     * TODO: video trong gallery (docs/design-vs-db-gaps.md #6); phí giao "từ 25.000" chưa có bảng khu vực (#7).
     */
    $sizes = $product->sizes->map(fn (\App\Models\ProductSize $size): array => [
        'id' => $size->id,
        'name' => $size->size_name,
        'price' => $size->price,
        'dimension' => $product->dimension_info,
    ])->all();
    $selectedSize = $product->sizes->firstWhere('is_default', true) ?? $product->sizes->first();
    $price = $selectedSize?->price ?? $product->base_price;

    $gallery = $product->images->isNotEmpty()
        ? $product->images->map(fn (\App\Models\ProductImage $image): array => ['url' => $image->url])->all()
        : [['image' => $product->slug]];
    $deliveryFeeFrom = 25000;

    /*
     * TODO: dữ liệu mẫu theo docs/designs/storefront/04-chi-tiet-san-pham.png — chỉ dựng giao diện.
     * Khi nối DB: lấy từ bảng addons (is_active). Dòng phụ "Hộp 6 viên" / "Lời nhắn riêng của bạn" (note) chưa có cột.
     * Ảnh: public/images/flowers/{image}.(webp|jpg|png); chưa có file thì hiện khung giữ chỗ.
     * Thiệp viết tay (miễn phí) luôn được chọn sẵn cho khách.
     */
    $addons = [
        ['id' => 1, 'name' => 'Hộp quà nhỏ', 'price' => 80000, 'is_free' => false, 'note' => null, 'image' => 'addon-hop-qua'],
        ['id' => 2, 'name' => 'Gấu bông êm ái', 'price' => 150000, 'is_free' => false, 'note' => null, 'image' => 'addon-gau-bong'],
        ['id' => 3, 'name' => 'Chocolate ngọt ngào', 'price' => 120000, 'is_free' => false, 'note' => 'Hộp 6 viên', 'image' => 'addon-chocolate'],
        ['id' => 4, 'name' => 'Thiệp viết tay', 'price' => 0, 'is_free' => true, 'note' => 'Lời nhắn riêng của bạn', 'image' => 'addon-thiep'],
    ];
    $preselectedAddons = collect($addons)->where('is_free', true)->pluck('id')->all();

    $button = 'inline-flex h-12 items-center justify-center gap-3 rounded px-6 text-[15px] font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $stepButton = 'grid size-11 place-items-center text-lh-heading transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-not-allowed disabled:text-lh-border disabled:hover:bg-transparent';
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20';
@endphp

<x-layouts.storefront :title="$product->name">
    <div class="{{ $container }} pt-5 pb-16 lg:pb-20">
        <x-storefront.breadcrumb :items="[
            [__('storefront.menu.home'), url('/')],
            [__('flowers.title'), route('flowers.index')],
            [$product->name, null],
        ]" />

        <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-16 xl:gap-[68px]" data-product>
            <x-storefront.product-gallery :product-name="$product->name" :items="$gallery" />

            <div class="lg:pt-2">
                <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ $product->category->name }}</p>
                <h1 class="mt-5 font-serif text-[40px] leading-tight sm:text-5xl">{{ $product->name }}</h1>
                <p class="mt-5 font-serif text-3xl tabular-nums" aria-live="polite" data-size-price>
                    <x-money :amount="$price" />
                </p>
                @if ($product->description)
                    <p class="mt-6 text-[15px] leading-relaxed text-lh-muted">{{ $product->description }}</p>
                @endif
                <div class="mt-7 border-t border-lh-border"></div>

                @if ($sizes !== [])
                    <x-storefront.size-picker class="mt-7" :sizes="$sizes" :selected="$selectedSize->id" />
                @endif

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

        <x-storefront.addon-picker class="mt-14 lg:mt-16" :addons="$addons" :selected="$preselectedAddons" />
    </div>
</x-layouts.storefront>
