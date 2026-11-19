@php
    /**
     * @var App\DTOs\Dashboard\RevenueChart $revenue
     * @var array{today: int, yesterday: int} $newOrders
     * @var array{today: int, yesterday: int} $newCustomers
     * @var array{active: int, lowStock: int, total: int} $products
     * @var int $awaitingConfirmation
     * @var int $flowersBeforeNoon
     * @var Illuminate\Database\Eloquent\Collection<int, App\Models\Order> $recentOrders
     * @var Illuminate\Database\Eloquent\Collection<int, App\Models\Product> $featuredProducts
     *
     * Dòng "lời nhắn cần phản hồi" của thiết kế chưa có dữ liệu (docs/design-vs-db-gaps.md #14) nên chưa hiện.
     * Đơn vị tồn kho (bó/bình/giỏ) chưa có cột (gap #4): cột Tồn kho chỉ hiện số.
     */
    use App\Actions\Dashboard\ShowAdminDashboardAction;

    $thousands = __('storefront.thousands_separator');
    $decimal = __('admin.dashboard.decimal_separator');
    $price = fn (int $amount): string => number_format($amount, 0, '', $thousands);
    $signed = fn (int|float $change, int $decimals = 0): string => ($change > 0 ? '+' : ($change < 0 ? '−' : '')).number_format(abs($change), $decimals, $decimal, $thousands);
    $pad = fn (int $count): string => str_pad((string) $count, 2, '0', STR_PAD_LEFT);

    $todayRevenueChange = $revenue->todayChangePercent();
    $todos = [
        ['count' => $awaitingConfirmation, 'label' => __('admin.dashboard.todos.awaiting'), 'url' => route('admin.orders', ['status' => 'received'])],
        ['count' => $products['lowStock'], 'label' => __('admin.dashboard.todos.low_stock'), 'url' => route('admin.products')],
    ];

    $panel = 'overflow-hidden rounded-md border border-lh-border bg-lh-surface';
    $headCell = 'px-4 py-5 font-normal whitespace-nowrap first:pl-6 last:pr-6';
    $cell = 'px-4 py-5 align-middle first:pl-6 last:pr-6';
    $textLink = 'inline-flex items-center gap-2 rounded text-sm whitespace-nowrap text-lh-heading underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $iconLink = 'inline-grid size-9 place-items-center rounded text-lh-heading transition-colors hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.admin :title="__('admin.menu.dashboard')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.dashboard.title')" :description="$description">
            <x-slot:actions>
                {{-- TODO: mở bộ chọn ngày để xem số liệu theo ngày. --}}
                <button type="button"
                    class="inline-flex h-12 items-center gap-4 rounded border border-lh-heading px-6 text-[15px] text-lh-heading transition-colors hover:bg-lh-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    {{ $todayLabel }}
                    <x-lucide-calendar-days class="size-5 stroke-[1.5]" aria-hidden="true" />
                </button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    {{-- 4 thẻ số liệu --}}
    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.dashboard.stats.revenue')" icon="lucide-wallet"
            :value="$price($revenue->today()->amount).' '.__('storefront.currency')"
            :note="$todayRevenueChange === null
                ? __('admin.dashboard.stats.revenue_note')
                : __('admin.dashboard.stats.revenue_change', ['change' => $signed($todayRevenueChange, 1)])" />
        <x-admin.stat-card :label="__('admin.dashboard.stats.new_orders')" icon="lucide-shopping-bag" :value="$newOrders['today']"
            :note="__('admin.dashboard.stats.new_orders_note', ['change' => $signed($newOrders['today'] - $newOrders['yesterday'])])" />
        <x-admin.stat-card :label="__('admin.dashboard.stats.new_customers')" icon="lucide-users" :value="$newCustomers['today']"
            :note="__('admin.dashboard.stats.new_customers_note', ['change' => $signed($newCustomers['today'] - $newCustomers['yesterday'])])" />
        <x-admin.stat-card :label="__('admin.dashboard.stats.products')" icon="lucide-flower" :value="$products['active']"
            :note="__('admin.dashboard.stats.products_note', ['count' => $products['lowStock']])" />
    </div>

    {{-- Biểu đồ doanh thu + việc cần làm --}}
    <div class="mt-8 grid items-start gap-8 xl:grid-cols-[minmax(0,1fr)_406px]">
        <x-admin.revenue-chart :chart="$revenue" />

        <section aria-labelledby="todos-title" class="rounded-md bg-lh-sage-50 px-6 py-7 sm:px-8">
            <h2 id="todos-title" class="font-serif text-[28px] leading-tight">{{ __('admin.dashboard.todos.title') }}</h2>
            <ul class="mt-5 flex flex-col gap-1">
                @foreach ($todos as $todo)
                    <li>
                        <a href="{{ $todo['url'] }}"
                            class="group flex items-center gap-4 rounded py-2.5 transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                            <span class="w-11 text-[30px] leading-none font-light text-lh-primary tabular-nums">{{ $pad($todo['count']) }}</span>
                            <span class="flex-1 text-[15px]">{{ $todo['label'] }}</span>
                            <x-lucide-arrow-up-right class="size-5 stroke-[1.5] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" aria-hidden="true" />
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="mt-6 text-[13px] text-lh-muted">
                <a href="{{ route('admin.orders.schedule') }}" class="rounded underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-lh-primary">
                    {{ $flowersBeforeNoon > 0
                        ? __('admin.dashboard.todos.before_noon', ['count' => $flowersBeforeNoon])
                        : __('admin.dashboard.todos.nothing_before_noon') }}
                </a>
            </p>
        </section>
    </div>

    {{-- Đơn hàng gần đây --}}
    <section aria-labelledby="recent-orders-title" class="{{ $panel }} mt-8">
        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-7">
            <h2 id="recent-orders-title" class="text-xl font-semibold">{{ __('admin.dashboard.recent_orders.title') }}</h2>
            <a href="{{ route('admin.orders') }}" class="{{ $textLink }}">
                {{ __('admin.dashboard.recent_orders.view_all') }}
                <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
            </a>
        </div>

        @if ($recentOrders->isEmpty())
            <p class="border-t border-lh-border px-6 py-10 text-center text-sm text-lh-muted">{{ __('admin.dashboard.recent_orders.empty') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-left">
                    <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                        <tr>
                            @foreach (['code', 'customer', 'flowers', 'total', 'status', 'time'] as $column)
                                <th scope="col" class="{{ $headCell }}">{{ __('admin.dashboard.recent_orders.columns.'.$column) }}</th>
                            @endforeach
                            <th scope="col" class="{{ $headCell }}"><span class="sr-only">{{ __('admin.dashboard.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $order)
                            @php
                                $flower = $order->items->first();
                            @endphp
                            <tr class="border-t border-lh-border">
                                <td class="{{ $cell }} text-[15px] font-semibold whitespace-nowrap">{{ $order->order_code }}</td>
                                <td class="{{ $cell }} text-[15px] font-semibold">{{ $order->deliveryDetail?->sender_name }}</td>
                                <td class="{{ $cell }} text-[15px] text-lh-muted">
                                    {{ $flower?->item_name }}
                                    @if ($order->items->count() > 1)
                                        · {{ __('admin.orders.more_items', ['count' => $order->items->count() - 1]) }}
                                    @endif
                                </td>
                                <td class="{{ $cell }} text-[15px] font-semibold tabular-nums whitespace-nowrap">{{ $price($order->total_amount) }}</td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$order->order_status->badgeTone()">{{ $order->order_status->adminLabel() }}</x-admin.badge>
                                </td>
                                <td class="{{ $cell }} whitespace-nowrap">
                                    @if ($order->deliveryDetail)
                                        <p class="text-[15px] tabular-nums">{{ $order->deliveryDetail->time_slot }}</p>
                                        <p class="mt-1 text-xs text-lh-muted">{{ $order->deliveryDetail->delivery_date->format('d/m/Y') }}</p>
                                    @endif
                                </td>
                                <td class="{{ $cell }} text-right">
                                    <a href="{{ route('admin.orders', ['q' => $order->order_code]) }}" class="{{ $iconLink }}"
                                        aria-label="{{ __('admin.dashboard.recent_orders.view', ['code' => $order->order_code]) }}">
                                        <x-lucide-ellipsis class="size-5 stroke-[1.5]" aria-hidden="true" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Quản lý sản phẩm (sản phẩm nổi bật) --}}
    <section aria-labelledby="featured-products-title" class="{{ $panel }} mt-8">
        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-7">
            <div>
                <h2 id="featured-products-title" class="text-xl font-semibold">{{ __('admin.dashboard.products.title') }}</h2>
                <p class="mt-1.5 text-[13px] text-lh-muted">{{ __('admin.dashboard.products.description', ['count' => $featuredProducts->count()]) }}</p>
            </div>
            {{-- TODO: trỏ tới form thêm sản phẩm khi có. --}}
            <x-admin.create-button :href="route('admin.products')">{{ __('admin.dashboard.products.create') }}</x-admin.create-button>
        </div>

        @if ($featuredProducts->isEmpty())
            <p class="border-t border-lh-border px-6 py-10 text-center text-sm text-lh-muted">{{ __('admin.dashboard.products.empty') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left">
                    <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                        <tr>
                            @foreach (['product', 'category', 'price', 'stock', 'status'] as $column)
                                <th scope="col" class="{{ $headCell }}">{{ __('admin.dashboard.products.columns.'.$column) }}</th>
                            @endforeach
                            <th scope="col" class="{{ $headCell }}"><span class="sr-only">{{ __('admin.dashboard.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($featuredProducts as $product)
                            @php
                                $isLowStock = $product->stock <= ShowAdminDashboardAction::LOW_STOCK_THRESHOLD;
                            @endphp
                            <tr class="border-t border-lh-border">
                                <td class="{{ $cell }}">
                                    <div class="flex items-center gap-4">
                                        @if ($product->primaryImage)
                                            <img src="{{ $product->primaryImage->url }}" alt="" loading="lazy" class="size-[58px] shrink-0 rounded object-cover">
                                        @else
                                            <x-flower-image :name="$product->slug" class="size-[58px] shrink-0 rounded" />
                                        @endif
                                        <p class="text-[15px] font-semibold">{{ $product->name }}</p>
                                    </div>
                                </td>
                                <td class="{{ $cell }} text-[15px] whitespace-nowrap text-lh-muted">{{ $product->category?->name }}</td>
                                <td class="{{ $cell }} text-[15px] font-semibold tabular-nums whitespace-nowrap">{{ $price($product->display_price) }}</td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$isLowStock ? 'warning' : 'success'" class="tabular-nums">
                                        {{ $product->stock }}
                                        @if ($isLowStock)
                                            <span class="sr-only">({{ __('admin.dashboard.todos.low_stock') }})</span>
                                        @endif
                                    </x-admin.badge>
                                </td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$product->stock === 0 ? 'warning' : 'success'">
                                        {{ __('admin.products.statuses.'.($product->stock === 0 ? 'out_of_stock' : 'selling')) }}
                                    </x-admin.badge>
                                </td>
                                <td class="{{ $cell }} text-right whitespace-nowrap">
                                    <a href="{{ route('admin.products.images', $product->slug) }}" class="{{ $iconLink }}"
                                        aria-label="{{ __('admin.list.edit_item', ['name' => $product->name]) }}">
                                        <x-lucide-pencil class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="flex flex-col gap-3 border-t border-lh-border px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-[13px] text-lh-muted">{{ __('admin.dashboard.products.showing', ['shown' => $featuredProducts->count(), 'total' => $products['total']]) }}</p>
            <a href="{{ route('admin.products') }}" class="{{ $textLink }}">
                {{ __('admin.dashboard.products.view_all') }}
                <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
            </a>
        </div>
    </section>
</x-layouts.admin>
