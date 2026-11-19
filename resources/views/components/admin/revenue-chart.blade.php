@props(['chart'])

@php
    /**
     * @var App\DTOs\Dashboard\RevenueChart $chart  7 ngày gần nhất, render sẵn; đổi 7/30 ngày gọi API admin.dashboard.revenue.
     *
     * Chart.js vẽ trên canvas (resources/js/dashboard-chart.js). Canvas không đọc được bằng trình đọc màn hình
     * nên bảng sr-only [data-chart-table] mang cùng số liệu và được JS cập nhật theo.
     * Màu cột lấy từ token: hôm nay --color-lh-primary (#355B46), các ngày trước --color-lh-sage-200 (#CBD8C6).
     */
    use App\Enums\ChartRange;
    use App\Http\Resources\RevenueChartResource;

    $data = (new RevenueChartResource($chart))->resolve();
    $days = $data['days'];
    $decimal = __('admin.dashboard.decimal_separator');
    $thousands = __('storefront.thousands_separator');
    $money = fn (int $amount): string => number_format($amount, 0, '', $thousands).' '.__('storefront.currency');
    $change = $data['changes']['revenue'];
    $isUp = $change === null || $change >= 0;
    $changeText = $change === null ? '' : number_format(abs($change), 1, $decimal, $thousands);

    $strings = [
        'title' => ['revenue' => __('admin.dashboard.revenue.title_revenue'), 'orders' => __('admin.dashboard.revenue.title_orders')],
        'unit' => ['revenue' => __('admin.dashboard.revenue.unit_revenue'), 'orders' => __('admin.dashboard.revenue.unit_orders')],
        'up' => __('admin.dashboard.revenue.up'),
        'down' => __('admin.dashboard.revenue.down'),
        'orders' => __('admin.dashboard.revenue.orders_count'),
        'today' => __('admin.dashboard.revenue.today'),
        'loading' => __('admin.dashboard.revenue.loading'),
        'error' => __('admin.dashboard.revenue.error'),
        'currency' => __('storefront.currency'),
        'locale' => __('admin.dashboard.number_locale'),
    ];
    $toggle = 'h-9 rounded px-3.5 text-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary aria-pressed:bg-lh-surface aria-pressed:font-medium aria-pressed:text-lh-heading aria-pressed:shadow-sm text-lh-muted hover:text-lh-heading';
@endphp

<section aria-labelledby="revenue-title" data-revenue-chart data-url="{{ route('admin.dashboard.revenue') }}"
    data-chart='@json($data)' data-strings='@json($strings)'
    {{ $attributes->class('rounded-md border border-lh-border bg-lh-surface px-6 py-7 sm:px-8') }}>
    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-2">
        <h2 id="revenue-title" class="text-xl font-semibold" data-chart-title>{{ __('admin.dashboard.revenue.title_revenue', ['days' => $days]) }}</h2>
        <p class="text-[13px] text-lh-muted" data-chart-unit>{{ __('admin.dashboard.revenue.unit_revenue') }}</p>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-4">
        <p class="text-[32px] leading-none font-semibold text-lh-primary-dark tabular-nums" data-chart-total>{{ $money($data['totals']['revenue']) }}</p>
        <x-admin.badge :tone="$isUp ? 'success' : 'warning'" class="gap-1" data-chart-change :hidden="$change === null">
            <x-lucide-arrow-up @class(['size-3.5 stroke-2', 'hidden' => ! $isUp]) data-change-up aria-hidden="true" />
            <x-lucide-arrow-down @class(['size-3.5 stroke-2', 'hidden' => $isUp]) data-change-down aria-hidden="true" />
            <span aria-hidden="true" data-change-value>{{ $changeText }}%</span>
            <span class="sr-only" data-change-label>{{ $change === null ? '' : __('admin.dashboard.revenue.'.($isUp ? 'up' : 'down'), ['change' => $changeText, 'days' => $days]) }}</span>
        </x-admin.badge>
    </div>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <div role="group" aria-label="{{ __('admin.dashboard.revenue.metric_label') }}" class="inline-flex rounded bg-lh-canvas p-1">
            @foreach (['revenue', 'orders'] as $metric)
                <button type="button" class="{{ $toggle }}" data-chart-metric="{{ $metric }}" aria-pressed="{{ $metric === 'revenue' ? 'true' : 'false' }}">
                    {{ __('admin.dashboard.revenue.metrics.'.$metric) }}
                </button>
            @endforeach
        </div>
        <div role="group" aria-label="{{ __('admin.dashboard.revenue.range_label') }}" class="inline-flex rounded bg-lh-canvas p-1">
            @foreach (ChartRange::cases() as $range)
                <button type="button" class="{{ $toggle }}" data-chart-range="{{ $range->value }}" aria-pressed="{{ $range->value === $days ? 'true' : 'false' }}">
                    {{ __('admin.dashboard.revenue.range', ['days' => $range->value]) }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="relative mt-6 h-[240px] transition-opacity aria-busy:opacity-50" data-chart-frame>
        <canvas aria-hidden="true" data-chart-canvas></canvas>
    </div>
    <p class="mt-3 min-h-4 text-xs text-lh-muted" role="status" data-chart-status></p>
    <p class="text-xs text-lh-muted">{{ __('admin.dashboard.revenue.note') }}</p>

    <table class="sr-only">
        <caption data-chart-caption>{{ __('admin.dashboard.revenue.title_revenue', ['days' => $days]) }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ __('admin.dashboard.revenue.date') }}</th>
                <th scope="col">{{ __('admin.dashboard.revenue.amount') }}</th>
                <th scope="col">{{ __('admin.dashboard.revenue.metrics.orders') }}</th>
            </tr>
        </thead>
        <tbody data-chart-table>
            @foreach ($data['dates'] as $index => $date)
                <tr>
                    <th scope="row">{{ $date }}{{ $loop->last ? ' ('.__('admin.dashboard.revenue.today').')' : '' }}</th>
                    <td>{{ $money($data['revenue'][$index]) }}</td>
                    <td>{{ $data['orders'][$index] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
