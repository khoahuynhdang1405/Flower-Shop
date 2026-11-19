@props(['chart'])

@php
    /**
     * @var App\DTOs\Dashboard\RevenueChart $chart
     *
     * Cột hôm nay màu xanh đậm (#355B46), các ngày trước sage (#CBD8C6). Màu sage có tương phản thấp với nền
     * trắng nên mỗi cột luôn có nhãn số ngay trên đầu và một bảng ẩn cho trình đọc màn hình.
     */
    $decimal = __('admin.dashboard.decimal_separator');
    $thousands = __('storefront.thousands_separator');
    $millions = fn (int $amount): string => rtrim(rtrim(number_format($amount / 1_000_000, 2, $decimal, $thousands), '0'), $decimal);
    $money = fn (int $amount): string => number_format($amount, 0, '', $thousands).' '.__('storefront.currency');
    $highest = max($chart->highest(), 1);
    $change = $chart->changePercent();
    $todayIndex = array_key_last($chart->days);
@endphp

<section aria-labelledby="revenue-title" {{ $attributes->class('rounded-md border border-lh-border bg-lh-surface px-6 py-7 sm:px-8') }}>
    <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-2">
        <h2 id="revenue-title" class="text-xl font-semibold">{{ __('admin.dashboard.revenue.title') }}</h2>
        <p class="text-[13px] text-lh-muted">{{ __('admin.dashboard.revenue.unit') }}</p>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-4">
        <p class="text-[32px] leading-none font-semibold text-lh-primary-dark tabular-nums">{{ $money($chart->total()) }}</p>
        @if ($change !== null)
            @php
                $isUp = $change >= 0;
                $changeText = number_format(abs($change), 1, $decimal, $thousands);
            @endphp
            <x-admin.badge :tone="$isUp ? 'success' : 'warning'" class="gap-1">
                <x-dynamic-component :component="$isUp ? 'lucide-arrow-up' : 'lucide-arrow-down'" class="size-3.5 stroke-2" aria-hidden="true" />
                <span aria-hidden="true">{{ $changeText }}%</span>
                <span class="sr-only">{{ __('admin.dashboard.revenue.'.($isUp ? 'up' : 'down'), ['change' => $changeText]) }}</span>
            </x-admin.badge>
        @endif
    </div>

    {{-- Phần hình: ẩn với trình đọc màn hình, bảng bên dưới mang cùng số liệu. --}}
    <ol class="mt-6 grid h-[200px] grid-cols-7 gap-2 pt-7 sm:gap-4" aria-hidden="true">
        @foreach ($chart->days as $index => $day)
            @php
                $isToday = $index === $todayIndex;
                $height = $day->amount > 0 ? max(2, round($day->amount / $highest * 100, 2)) : 0;
            @endphp
            <li class="flex h-full items-end justify-center" title="{{ $day->date->format('d/m') }} · {{ $money($day->amount) }}">
                <span @class([
                    'relative block w-full max-w-[58px] rounded-t transition-opacity hover:opacity-85',
                    'bg-lh-primary' => $isToday,
                    'bg-lh-sage-200' => ! $isToday,
                ]) style="height: {{ $height }}%">
                    <span class="absolute bottom-full left-1/2 mb-2 -translate-x-1/2 text-xs whitespace-nowrap text-lh-muted tabular-nums sm:text-[13px]">{{ $millions($day->amount) }}</span>
                </span>
            </li>
        @endforeach
    </ol>
    <ol class="mt-3 grid grid-cols-7 gap-2 border-t border-lh-border pt-3 sm:gap-4" aria-hidden="true">
        @foreach ($chart->days as $index => $day)
            <li @class(['text-center text-xs tabular-nums sm:text-[13px]', 'font-semibold text-lh-heading' => $index === $todayIndex, 'text-lh-muted' => $index !== $todayIndex])>
                {{ $day->date->format('d/m') }}
            </li>
        @endforeach
    </ol>

    <p class="mt-4 text-xs text-lh-muted">{{ __('admin.dashboard.revenue.note') }}</p>

    <table class="sr-only">
        <caption>{{ __('admin.dashboard.revenue.title') }}</caption>
        <thead>
            <tr>
                <th scope="col">{{ __('admin.dashboard.revenue.date') }}</th>
                <th scope="col">{{ __('admin.dashboard.revenue.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($chart->days as $index => $day)
                <tr>
                    <th scope="row">{{ $day->date->format('d/m/Y') }}{{ $index === $todayIndex ? ' ('.__('admin.dashboard.revenue.today').')' : '' }}</th>
                    <td>{{ $money($day->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
