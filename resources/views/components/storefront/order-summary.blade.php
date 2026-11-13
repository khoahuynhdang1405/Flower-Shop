@props([
    /** @var \App\Models\Order $order (kèm items, deliveryDetail, promotion) */
    'order',
])

@use('App\Enums\CartItemType')

@php
    $flowers = $order->items->where('item_type', CartItemType::Flower);
    $addons = $order->items->where('item_type', CartItemType::Addon);
    $row = 'flex items-baseline justify-between gap-4 text-sm';
@endphp

{{-- Khối "Đơn hoa của bạn" ở trang xác nhận: các món (snapshot lúc đặt), tạm tính / ưu đãi / phí giao, tổng. --}}
<section aria-labelledby="order-summary-title" {{ $attributes->class('rounded-md bg-lh-addon-selected p-6 sm:p-8') }}>
    <h2 id="order-summary-title" class="font-serif text-[26px] leading-tight text-lh-primary-dark sm:text-[28px]">{{ __('checkout.summary.title') }}</h2>

    <ul class="mt-6 space-y-5">
        @foreach ($flowers as $flower)
            <li class="flex gap-4">
                @if ($flower->product?->primaryImage)
                    <img src="{{ $flower->product->primaryImage->url }}" alt="" loading="lazy" class="aspect-square w-[72px] shrink-0 rounded-sm bg-lh-sage-100 object-cover">
                @else
                    <x-flower-image :name="$flower->product->slug ?? ''" class="aspect-square w-[72px] shrink-0 rounded-sm" />
                @endif
                <div class="min-w-0">
                    <h3 class="font-serif text-xl leading-tight text-lh-primary-dark">{{ $flower->item_name }}</h3>
                    <p class="mt-1 text-xs text-lh-addon-muted">
                        {{ $flower->size_name
                            ? __('checkout.summary.flower_meta', ['size' => Str::lower($flower->size_name), 'quantity' => $flower->quantity])
                            : __('checkout.summary.flower_meta_no_size', ['quantity' => $flower->quantity]) }}
                    </p>
                    <x-money :amount="$flower->total_price" class="mt-1.5 block text-sm text-lh-primary-dark tabular-nums" />
                </div>
            </li>
        @endforeach
    </ul>

    @if ($addons->isNotEmpty())
        <dl class="mt-6 space-y-3">
            @foreach ($addons as $addon)
                <div class="{{ $row }}">
                    <dt class="text-lh-addon-muted">{{ __('checkout.summary.addon', ['name' => $addon->item_name, 'quantity' => $addon->quantity]) }}</dt>
                    <dd class="text-lh-primary-dark tabular-nums">
                        @if ($addon->total_price === 0)
                            {{ __('checkout.summary.free') }}
                        @else
                            <x-money :amount="$addon->total_price" />
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    @endif

    <dl class="mt-6 space-y-3 border-t border-lh-border pt-6">
        <div class="{{ $row }}">
            <dt class="text-lh-addon-muted">{{ __('checkout.summary.subtotal') }}</dt>
            <dd class="text-lh-primary-dark tabular-nums"><x-money :amount="$order->subtotal" /></dd>
        </div>
        @if ($order->discount_amount > 0)
            <div class="{{ $row }}">
                <dt class="text-lh-addon-muted">{{ __('checkout.summary.discount', ['code' => $order->promotion?->code]) }}</dt>
                <dd class="text-lh-primary-dark tabular-nums">−<x-money :amount="$order->discount_amount" /></dd>
            </div>
        @endif
        <div class="{{ $row }}">
            <dt class="text-lh-addon-muted">
                {{ __('checkout.summary.shipping_district', ['district' => $order->deliveryDetail->shipping_district, 'city' => config('shop.city')]) }}
            </dt>
            <dd class="text-lh-primary-dark tabular-nums"><x-money :amount="$order->shipping_fee" /></dd>
        </div>
    </dl>

    <div class="mt-6 flex items-baseline justify-between gap-4 border-t border-lh-border pt-6">
        <p class="text-base text-lh-primary-dark">{{ __('checkout.summary.total') }}</p>
        <x-money :amount="$order->total_amount" class="font-serif text-2xl leading-none whitespace-nowrap text-lh-primary-dark lining-nums tabular-nums sm:text-[30px]" />
    </div>
    <p class="mt-3 text-xs text-lh-addon-muted">{{ __('checkout.summary.total_note') }}</p>
</section>
