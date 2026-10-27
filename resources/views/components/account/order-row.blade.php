@props(['order'])

@php
    $isPreparing = $order['status'] === 'preparing';
    $actionLink = 'inline-flex items-center gap-1 rounded text-xs hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

{{-- Một dòng trong "Những lần gửi thương". data-filter-value dùng cho bộ lọc chip (resources/js/filter-chips.js). --}}
<li data-filter-item data-filter-value="{{ $isPreparing ? 'processing' : 'completed' }}"
    class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-4 gap-y-3 border-t border-lh-border px-5 py-5 sm:px-7 md:grid-cols-[auto_minmax(0,1fr)_minmax(0,1fr)_170px]">
    <x-flower-image :name="$order['image']" class="size-15 rounded-sm" />

    <div class="min-w-0">
        <p class="truncate text-sm font-semibold">{{ $order['name'] }}</p>
        <p class="mt-1.5 text-xs text-lh-muted">
            {{ __('account.orders.meta', ['code' => $order['code'], 'date' => $order['date'], 'count' => $order['items']]) }}
        </p>
    </div>

    <div class="flex items-center gap-3 max-md:col-start-2 md:flex-col md:items-start md:gap-1.5">
        <x-money :amount="$order['total']" class="text-sm font-semibold" />
        <span @class([
            'rounded-full px-2.5 py-1 text-xs',
            'bg-lh-sage-100' => $isPreparing,
            'bg-lh-sage-50' => ! $isPreparing,
        ])>{{ __('account.orders.status.'.$order['status']) }}</span>
    </div>

    <div class="flex items-center gap-4 max-md:col-start-2 md:flex-col md:items-start md:gap-1.5 md:justify-self-end">
        {{-- TODO: trang chi tiết đơn / đặt lại hoa. --}}
        <a href="#" class="{{ $actionLink }}">
            {{ __('account.orders.view') }}
            <x-lucide-arrow-right class="size-3.5 stroke-[1.5]" aria-hidden="true" />
        </a>
        @if ($isPreparing)
            <span class="text-xs text-lh-muted">{{ __('account.orders.deliver_today', ['time' => $order['delivery_time']]) }}</span>
        @else
            <a href="#" class="{{ $actionLink }}">{{ __('account.orders.reorder') }}</a>
        @endif
    </div>
</li>
