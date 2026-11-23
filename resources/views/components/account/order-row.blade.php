@props([
    /** @var \App\Models\Order $order kèm deliveryDetail, items (dòng hoa có product.primaryImage) */
    'order',
])

@php
    use App\Enums\CartItemType;
    use App\Enums\OrderStatus;

    $status = $order->order_status;
    $isInProgress = $status->isInProgress();
    $delivery = $order->deliveryDetail;
    $flower = $order->items->firstWhere('item_type', CartItemType::Flower);
    $product = $flower?->product;
    $filterValue = match (true) {
        $isInProgress => 'processing',
        $status === OrderStatus::Completed => 'completed',
        default => 'cancelled',
    };
    $deliveryLine = $delivery && $isInProgress
        ? ($delivery->delivery_date->isToday()
            ? __('account.orders.deliver_today', ['time' => str_replace('-', '–', $delivery->time_slot)])
            : __('account.orders.deliver_on', ['date' => $delivery->delivery_date->format('d/m/Y'), 'time' => str_replace('-', '–', $delivery->time_slot)]))
        : null;
    $actionLink = 'inline-flex items-center gap-1 rounded text-xs hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

{{-- Một dòng trong "Những lần gửi thương". data-filter-value dùng cho bộ lọc chip (resources/js/filter-chips.js). --}}
<li data-filter-item data-filter-value="{{ $filterValue }}"
    class="grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-4 gap-y-3 border-t border-lh-border px-5 py-5 sm:px-7 md:grid-cols-[auto_minmax(0,1fr)_minmax(0,1fr)_170px]">
    @if ($product?->primaryImage)
        <img src="{{ $product->primaryImage->url }}" alt="" loading="lazy" class="size-15 rounded-sm bg-lh-sage-100 object-cover">
    @else
        <x-flower-image :name="$product->slug ?? ''" class="size-15 rounded-sm" />
    @endif

    <div class="min-w-0">
        <p class="truncate text-sm font-semibold">{{ $flower->item_name ?? $order->order_code }}</p>
        <p class="mt-1.5 text-xs text-lh-muted">
            {{ __('account.orders.meta', ['code' => $order->order_code, 'date' => $order->created_at->format('d/m/Y'), 'count' => $order->items->sum('quantity')]) }}
        </p>
    </div>

    <div class="flex items-center gap-3 max-md:col-start-2 md:flex-col md:items-start md:gap-1.5">
        <x-money :amount="$order->total_amount" class="text-sm font-semibold" />
        <span @class([
            'rounded-full px-2.5 py-1 text-xs',
            'bg-lh-sage-100' => $isInProgress,
            'bg-lh-sage-50' => ! $isInProgress,
        ])>{{ $status->customerLabel() }}</span>
    </div>

    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 max-md:col-start-2 md:flex-col md:items-start md:justify-self-end">
        @if ($delivery)
            <a href="{{ route('orders.track', ['code' => $order->order_code, 'phone' => $delivery->sender_phone]) }}" class="{{ $actionLink }}"
                aria-label="{{ __('account.orders.view_label', ['code' => $order->order_code]) }}">
                {{ __('account.orders.view') }}
                <x-lucide-arrow-right class="size-3.5 stroke-[1.5]" aria-hidden="true" />
            </a>
        @endif
        @if ($deliveryLine)
            <span class="text-xs text-lh-muted">{{ $deliveryLine }}</span>
        @elseif ($product?->is_active)
            <a href="{{ route('flowers.show', $product->slug) }}" class="{{ $actionLink }}">{{ __('account.orders.reorder') }}</a>
        @endif
    </div>
</li>
