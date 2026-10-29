@php
    /*
     * TODO: dữ liệu mẫu theo docs/designs/admin/03-san-pham-danh-gia.png — chỉ dựng giao diện.
     * Khi làm backend: giá từng size lấy từ product_sizes (size_name, price, is_default), tồn kho từ products.stock,
     * trạng thái từ products.is_active. Mã sản phẩm (LH-H001) và đơn vị tồn kho (bó/bình/giỏ) chưa có cột
     * (docs/design-vs-db-gaps.md #4). Khối đánh giá khách chờ bảng reviews (gap #1).
     */
    $lowStockThreshold = 5;
    $products = collect([
        ['name' => 'Nắng trong veo', 'slug' => 'nang-trong-veo', 'sku' => 'LH-H001', 'category' => 'bouquet', 'stock' => 18, 'is_active' => true,
            'sizes' => ['small' => 450000, 'medium' => 550000, 'large' => 750000], 'default' => 'medium'],
        ['name' => 'Lời thì thầm', 'slug' => 'loi-thi-tham', 'sku' => 'LH-H002', 'category' => 'bouquet', 'stock' => 12, 'is_active' => true,
            'sizes' => ['small' => 590000, 'medium' => 690000, 'large' => 890000], 'default' => 'medium'],
        ['name' => 'Thu dịu dàng', 'slug' => 'thu-diu-dang', 'sku' => 'LH-H003', 'category' => 'vase', 'stock' => 3, 'is_active' => true,
            'sizes' => ['medium' => 890000, 'large' => 1190000], 'default' => 'medium'],
        ['name' => 'Bình yên xanh', 'slug' => 'binh-yen-xanh', 'sku' => 'LH-H004', 'category' => 'basket', 'stock' => 10, 'is_active' => true,
            'sizes' => ['medium' => 750000, 'large' => 950000], 'default' => 'medium'],
        ['name' => 'Một ngày bình dị', 'slug' => 'mot-ngay-binh-di', 'sku' => 'LH-H005', 'category' => 'vase', 'stock' => 8, 'is_active' => true,
            'sizes' => ['small' => 490000, 'medium' => 590000, 'large' => 790000], 'default' => 'medium'],
        ['name' => 'Hồng phấn ban mai', 'slug' => 'hong-phan-ban-mai', 'sku' => 'LH-H006', 'category' => 'bouquet', 'stock' => 2, 'is_active' => true,
            'sizes' => ['small' => 420000, 'medium' => 520000], 'default' => 'small'],
        ['name' => 'Ấm áp mùa đông', 'slug' => 'am-ap-mua-dong', 'sku' => 'LH-H007', 'category' => 'basket', 'stock' => 0, 'is_active' => true,
            'sizes' => ['medium' => 980000], 'default' => 'medium'],
        ['name' => 'Cúc họa mi tháng mười', 'slug' => 'cuc-hoa-mi-thang-muoi', 'sku' => 'LH-H008', 'category' => 'bouquet', 'stock' => 15, 'is_active' => false,
            'sizes' => ['small' => 380000, 'medium' => 480000], 'default' => 'small'],
    ])->map(fn (array $product): array => [
        ...$product,
        'status' => match (true) {
            ! $product['is_active'] => 'hidden',
            $product['stock'] === 0 => 'out_of_stock',
            default => 'selling',
        },
    ]);

    $stats = [
        'selling' => $products->where('status', 'selling')->count(),
        'low_stock' => $products->where('is_active', true)->filter(fn (array $product): bool => $product['stock'] > 0 && $product['stock'] < $lowStockThreshold)->count(),
        'out_of_stock' => $products->where('status', 'out_of_stock')->count(),
        'hidden' => $products->where('status', 'hidden')->count(),
    ];

    $price = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator'));
    $statusTones = ['selling' => 'success', 'out_of_stock' => 'warning', 'hidden' => 'neutral'];
    $cell = 'px-4 py-5 first:pl-6 last:pr-6 align-middle';
    $editLink = 'inline-flex items-center gap-2 rounded text-sm whitespace-nowrap transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.admin :title="__('admin.menu.products')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.products.title')" :description="__('admin.products.description')">
            <x-slot:actions>
                {{-- TODO: form thêm sản phẩm. --}}
                <x-admin.create-button>{{ __('admin.products.create') }}</x-admin.create-button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.products.stats.selling')" :value="$stats['selling']"
            :note="__('admin.products.stats.selling_note')" />
        <x-admin.stat-card :label="__('admin.products.stats.low_stock')" :value="$stats['low_stock']"
            :note="__('admin.products.stats.low_stock_note', ['threshold' => $lowStockThreshold])" />
        <x-admin.stat-card :label="__('admin.products.stats.out_of_stock')" :value="$stats['out_of_stock']"
            :note="__('admin.products.stats.out_of_stock_note')" />
        <x-admin.stat-card :label="__('admin.products.stats.hidden')" :value="$stats['hidden']"
            :note="__('admin.products.stats.hidden_note')" />
    </div>

    <section data-list-filter aria-labelledby="products-table-title" class="mt-8 overflow-hidden rounded-md border border-lh-border bg-lh-surface">
        <div class="px-6 pt-8">
            <h2 id="products-table-title" class="text-xl font-semibold">{{ __('admin.products.table_title') }}</h2>
            <p class="mt-2 text-[13px] text-lh-muted">
                {{ __('admin.products.table_description', ['count' => $stats['selling'], 'time' => now()->format('H:i, d/m/Y')]) }}
            </p>

            <x-admin.list-toolbar :placeholder="__('admin.products.search_placeholder')" class="mt-10 mb-6"
                :filters="[
                    ['key' => 'category', 'label' => __('admin.products.filter_category'), 'all' => __('admin.products.all_categories'), 'options' => __('admin.products.categories')],
                    ['key' => 'status', 'label' => __('admin.products.filter_status'), 'all' => __('admin.products.all_statuses'), 'options' => __('admin.products.statuses')],
                ]" />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left">
                <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                    <tr>
                        @foreach (['product', 'category', 'prices', 'stock', 'status', 'actions'] as $column)
                            <th scope="col" class="px-4 py-5 font-normal whitespace-nowrap first:pl-6 last:pr-6">{{ __('admin.products.columns.'.$column) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody aria-live="polite">
                    @foreach ($products as $product)
                        @php
                            $isLowStock = $product['stock'] < $lowStockThreshold;
                        @endphp
                        <tr data-list-item data-search="{{ $product['name'] }} {{ $product['sku'] }}"
                            data-category="{{ $product['category'] }}" data-status="{{ $product['status'] }}" class="border-t border-lh-border">
                            <td class="{{ $cell }}">
                                <div class="flex items-center gap-4">
                                    <x-flower-image :name="$product['slug']" :alt="__('admin.products.image_alt', ['name' => $product['name']])"
                                        class="size-[58px] shrink-0 rounded" />
                                    <div class="min-w-0">
                                        <p class="text-[15px] font-semibold">{{ $product['name'] }}</p>
                                        <p class="mt-1.5 text-xs text-lh-muted">{{ $product['sku'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="{{ $cell }} text-[15px] whitespace-nowrap">{{ __('admin.products.categories.'.$product['category']) }}</td>
                            <td class="{{ $cell }}">
                                <dl class="grid w-max grid-cols-[3.75rem_5rem_auto] items-center gap-x-3 gap-y-1.5 text-sm">
                                    @foreach ($product['sizes'] as $size => $amount)
                                        @php
                                            $isDefault = $size === $product['default'];
                                        @endphp
                                        <dt @class(['text-lh-muted', 'font-medium text-lh-heading' => $isDefault])>{{ __('admin.products.sizes.'.$size) }}</dt>
                                        <dd @class(['text-right tabular-nums', 'font-semibold' => $isDefault])>{{ $price($amount) }}</dd>
                                        <dd>
                                            @if ($isDefault)
                                                <span class="rounded-full bg-lh-sage-50 px-2 py-0.5 text-[11px] text-lh-primary">{{ __('admin.products.default_size') }}</span>
                                            @endif
                                        </dd>
                                    @endforeach
                                </dl>
                            </td>
                            <td class="{{ $cell }}">
                                <x-admin.badge :tone="$isLowStock ? 'warning' : 'success'">
                                    {{ trans_choice('admin.products.units.'.$product['category'], $product['stock']) }}
                                    @if ($isLowStock && $product['stock'] > 0)
                                        <span class="sr-only">({{ __('admin.products.low_stock_hint') }})</span>
                                    @endif
                                </x-admin.badge>
                            </td>
                            <td class="{{ $cell }}">
                                <x-admin.badge :tone="$statusTones[$product['status']]">{{ __('admin.products.statuses.'.$product['status']) }}</x-admin.badge>
                            </td>
                            <td class="{{ $cell }}">
                                {{-- TODO: trang sửa sản phẩm (giá từng size, tồn kho). --}}
                                <a href="#" class="{{ $editLink }}" aria-label="{{ __('admin.list.edit_item', ['name' => $product['name']]) }}">
                                    <x-lucide-pencil class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                                    {{ __('admin.list.edit') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p data-list-empty hidden class="border-t border-lh-border px-6 py-10 text-center text-sm text-lh-muted">{{ __('admin.list.empty') }}</p>
        <p data-list-count data-all="{{ __('admin.products.showing_all', ['total' => ':total']) }}" data-some="{{ __('admin.products.showing_some', ['shown' => ':shown', 'total' => ':total']) }}"
            class="border-t border-lh-border px-6 py-6 text-[13px] text-lh-muted" aria-live="polite">{{ __('admin.products.showing_all', ['total' => $products->count()]) }}</p>
    </section>
</x-layouts.admin>
