@php
    /**
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $orders
     * @var array<string, int> $tabCounts
     * @var array<string, int> $stats
     * @var \App\DTOs\Order\AdminOrderFilters $filters
     */
    use App\Enums\OrderStatus;
    use App\Enums\PaymentMethod;
    use App\Enums\PaymentStatus;

    $activeTab = $filters->tab?->value ?? '';
    $isFiltered = $filters->tab !== null || $filters->search !== null || $filters->payment !== null;
    $tabUrl = fn (string $status): string => route('admin.orders', array_filter([
        'status' => $status,
        'q' => $filters->search,
        'payment' => $filters->payment?->value,
    ]));
    $change = $stats['todayChange'];
    $cell = 'px-4 py-6 align-middle first:pl-6 last:pr-6';
@endphp

<x-layouts.admin :title="__('admin.menu.orders')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.orders.title')" :description="__('admin.orders.description')" />
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <div class="mt-8 grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.orders.stats.today')" :value="$stats['today']"
            :note="__('admin.orders.stats.today_note', [
                'date' => now()->format('d/m/Y'),
                'change' => ($change > 0 ? '+' : '').$change,
            ])" />
        <x-admin.stat-card :label="__('admin.orders.stats.received')" :value="$stats['received']"
            :note="__('admin.orders.stats.received_note')" />
        <x-admin.stat-card :label="__('admin.orders.stats.preparing')" :value="$stats['preparing']"
            :note="__('admin.orders.stats.preparing_note')" />
        <x-admin.stat-card :label="__('admin.orders.stats.shipping')" :value="$stats['shipping']"
            :note="__('admin.orders.stats.shipping_note', ['completed' => $stats['completedToday'], 'cancelled' => $stats['cancelledToday']])" />
    </div>

    <section aria-labelledby="orders-table-title" class="mt-8 overflow-hidden rounded-md border border-lh-border bg-lh-surface">
        <div class="px-6 pt-8">
            <h2 id="orders-table-title" class="text-xl font-semibold">{{ __('admin.orders.table_title') }}</h2>
            <p class="mt-2 text-[13px] text-lh-muted">{{ __('admin.orders.table_description') }}</p>

            <form method="GET" action="{{ route('admin.orders') }}" role="search" class="mt-10">
                @if ($activeTab !== '')
                    <input type="hidden" name="status" value="{{ $activeTab }}">
                @endif
                <x-admin.list-toolbar server :search="$filters->search" :placeholder="__('admin.orders.search_placeholder')"
                    :filters="[[
                        'key' => 'payment',
                        'label' => __('admin.orders.filter_payment'),
                        'all' => __('admin.orders.all_payments'),
                        'options' => collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status) => [$status->value => $status->label()])->all(),
                        'value' => $filters->payment?->value,
                    ]]" />
            </form>

            <nav aria-label="{{ __('admin.orders.tabs_label') }}" class="-mx-6 mt-6 overflow-x-auto px-6 pb-6">
                <ul class="flex w-max gap-3" role="list">
                    @foreach (['' => __('admin.orders.tab_all')] + collect(OrderStatus::adminTabs())->mapWithKeys(fn (OrderStatus $tab) => [$tab->value => $tab->adminLabel()])->all() as $value => $label)
                        <li>
                            <a href="{{ $tabUrl($value) }}" @if ($value === $activeTab) aria-current="page" @endif
                                class="inline-flex h-8 items-center rounded-full px-3.5 text-[13px] whitespace-nowrap text-lh-muted transition-colors
                                    hover:bg-lh-sage-50 hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary
                                    aria-[current=page]:bg-lh-sage-100 aria-[current=page]:font-medium aria-[current=page]:text-lh-heading bg-lh-canvas">
                                {{ $label }} ({{ $tabCounts[$value] ?? 0 }})
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        @if ($orders->isEmpty())
            <div class="border-t border-lh-border px-6 py-14 text-center">
                @if ($isFiltered)
                    <p class="text-sm text-lh-muted">{{ __('admin.orders.empty_filtered') }}</p>
                    <a href="{{ route('admin.orders') }}"
                        class="mt-4 inline-block rounded text-sm font-medium text-lh-primary underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        {{ __('admin.orders.clear_filters') }}
                    </a>
                @else
                    <p class="font-serif text-2xl">{{ __('admin.orders.empty_title') }}</p>
                    <p class="mt-2 text-sm text-lh-muted">{{ __('admin.orders.empty_body') }}</p>
                @endif
            </div>
        @else
            <div class="relative overflow-x-auto">
                <table class="w-full min-w-[1040px] text-left">
                    <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                        <tr>
                            @foreach (['code', 'customer', 'total', 'payment', 'delivery', 'time', 'actions'] as $column)
                                <th scope="col" class="px-4 py-5 font-normal whitespace-nowrap first:pl-6 last:pr-6">{{ __('admin.orders.columns.'.$column) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            @php
                                $detail = $order->deliveryDetail;
                                $flower = $order->items->first();
                                $next = $order->order_status->next();
                            @endphp
                            <tr class="border-t border-lh-border">
                                <td class="{{ $cell }} whitespace-nowrap">
                                    <p class="text-[15px] font-semibold">{{ $order->order_code }}</p>
                                    <p class="mt-1.5 text-xs text-lh-muted">{{ $order->created_at->format('d/m/Y') }}</p>
                                </td>
                                <td class="{{ $cell }}">
                                    <p class="text-[15px] font-semibold">{{ $detail?->sender_name }}</p>
                                    <p class="mt-1.5 text-[13px] text-lh-muted">
                                        {{ $flower?->item_name }}
                                        @if ($order->items->count() > 1)
                                            · {{ __('admin.orders.more_items', ['count' => $order->items->count() - 1]) }}
                                        @endif
                                    </p>
                                </td>
                                <td class="{{ $cell }} text-[15px] tabular-nums whitespace-nowrap">
                                    {{ number_format($order->total_amount, 0, '', __('storefront.thousands_separator')) }}
                                </td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$order->payment_status->badgeTone()">{{ $order->payment_status->label() }}</x-admin.badge>
                                    <p class="mt-1.5 text-xs text-lh-muted">
                                        {{ $order->payment_method === PaymentMethod::Cod ? __('admin.orders.cod') : $order->payment_method->label() }}
                                    </p>
                                </td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$order->order_status->badgeTone()">{{ $order->order_status->adminLabel() }}</x-admin.badge>
                                </td>
                                <td class="{{ $cell }} whitespace-nowrap">
                                    @if ($detail)
                                        <p class="text-[15px] tabular-nums">{{ $detail->time_slot }}</p>
                                        <p class="mt-1.5 text-xs text-lh-muted">{{ $detail->delivery_date->format('d/m/Y') }}</p>
                                    @endif
                                </td>
                                <td class="{{ $cell }} whitespace-nowrap">
                                    @if ($next)
                                        @php($action = __('admin.orders.advance.'.$order->order_status->value))
                                        {{-- Bấm hai lần vẫn an toàn: chỉ lần đầu đổi được trạng thái (AdvanceOrderStatusAction). --}}
                                        <form method="POST" action="{{ route('admin.orders.advance', $order) }}">
                                            @csrf
                                            <button type="submit" aria-label="{{ __('admin.orders.advance_label', ['action' => $action, 'code' => $order->order_code]) }}"
                                                class="inline-flex h-9 items-center gap-1.5 rounded border border-lh-primary px-3.5 text-sm text-lh-primary transition-colors
                                                    hover:bg-lh-primary hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                                                {{ $action }}
                                                <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-4 border-t border-lh-border px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[13px] text-lh-muted">{{ __('admin.orders.showing', ['shown' => $orders->count(), 'total' => $orders->total()]) }}</p>
                <x-admin.pagination :paginator="$orders" />
            </div>
        @endif
    </section>

    <aside class="mt-8 flex flex-col gap-5 rounded-md bg-lh-sage-50 px-6 py-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[15px] font-semibold">{{ __('admin.orders.reminder.title') }}</p>
            <p class="mt-2 text-sm text-lh-muted">{{ __('admin.orders.reminder.body') }}</p>
        </div>
        <a href="{{ route('admin.orders.schedule') }}"
            class="inline-flex h-12 shrink-0 items-center justify-center rounded border border-lh-primary-dark bg-lh-surface px-6 text-[15px] text-lh-heading transition-colors
                hover:bg-lh-primary hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
            {{ __('admin.orders.reminder.action') }}
        </a>
    </aside>
</x-layouts.admin>
