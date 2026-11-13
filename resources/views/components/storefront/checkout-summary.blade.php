@props([
    /** @var \App\DTOs\Cart\CartSummaryData $summary */
    'summary',
    // id của <form> thanh toán: nút "Đặt hoa" nằm ngoài form nên gắn qua thuộc tính form="".
    'form',
])

@use('App\Enums\PromotionState')

@php
    $lines = $summary->lines;
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $row = 'flex items-baseline justify-between gap-4 text-sm';
    $city = config('shop.city');
@endphp

{{-- Khối "Đơn hoa của bạn": các món, tạm tính / ưu đãi / phí giao, tổng thanh toán, nút đặt hoa. --}}
<section aria-labelledby="checkout-summary-title" {{ $attributes->class('rounded-md bg-lh-addon-selected p-6 sm:p-8') }}>
    <h2 id="checkout-summary-title" class="font-serif text-[26px] leading-tight text-lh-primary-dark sm:text-[28px]">{{ __('checkout.summary.title') }}</h2>

    <ul class="mt-6 space-y-5">
        @foreach ($lines->flowers as $flower)
            <li class="flex gap-4">
                @if ($flower->imageUrl)
                    <img src="{{ $flower->imageUrl }}" alt="" loading="lazy" class="aspect-square w-[72px] shrink-0 rounded-sm bg-lh-sage-100 object-cover">
                @else
                    <x-flower-image :name="$flower->slug" class="aspect-square w-[72px] shrink-0 rounded-sm" />
                @endif
                <div class="min-w-0">
                    <h3 class="font-serif text-xl leading-tight text-lh-primary-dark">{{ $flower->name }}</h3>
                    <p class="mt-1 text-xs text-lh-addon-muted">
                        {{ $flower->sizeName
                            ? __('checkout.summary.flower_meta', ['size' => Str::lower($flower->sizeName), 'quantity' => $flower->quantity])
                            : __('checkout.summary.flower_meta_no_size', ['quantity' => $flower->quantity]) }}
                    </p>
                    <x-money :amount="$flower->total()" class="mt-1.5 block text-sm text-lh-primary-dark tabular-nums" />
                </div>
            </li>
        @endforeach
    </ul>

    @if ($lines->addons !== [])
        <dl class="mt-6 space-y-3">
            @foreach ($lines->addons as $addon)
                <div class="{{ $row }}">
                    <dt class="text-lh-addon-muted">{{ __('checkout.summary.addon', ['name' => $addon->name, 'quantity' => $addon->quantity]) }}</dt>
                    <dd class="text-lh-primary-dark tabular-nums">
                        @if ($addon->isFree)
                            {{ __('checkout.summary.free') }}
                        @else
                            <x-money :amount="$addon->total()" />
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    @endif

    <dl class="mt-6 space-y-3 border-t border-lh-border pt-6">
        <div class="{{ $row }}">
            <dt class="text-lh-addon-muted">{{ __('checkout.summary.subtotal') }}</dt>
            <dd class="text-lh-primary-dark tabular-nums"><x-money :amount="$summary->subtotal" /></dd>
        </div>
        @if ($summary->promotionState === PromotionState::Applied)
            <div class="{{ $row }}">
                <dt class="text-lh-addon-muted">{{ __('checkout.summary.discount', ['code' => $summary->promotionCode]) }}</dt>
                <dd class="text-lh-primary-dark tabular-nums">−<x-money :amount="$summary->discount" /></dd>
            </div>
        @endif
        <div class="{{ $row }}">
            {{-- resources/js/checkout.js thay nhãn theo khu vực giao đang chọn. --}}
            <dt class="text-lh-addon-muted" data-shipping-label
                data-template="{{ __('checkout.summary.shipping_district', ['district' => ':district', 'city' => $city]) }}">
                {{ __('checkout.summary.shipping', ['city' => $city]) }}
            </dt>
            <dd class="text-lh-primary-dark tabular-nums"><x-money :amount="$summary->shippingFee" /></dd>
        </div>
    </dl>

    <div class="mt-6 flex items-baseline justify-between gap-4 border-t border-lh-border pt-6">
        <p class="text-base text-lh-primary-dark">{{ __('checkout.summary.total') }}</p>
        <x-money :amount="$summary->total" class="font-serif text-2xl leading-none whitespace-nowrap text-lh-primary-dark lining-nums tabular-nums sm:text-[30px]" />
    </div>
    <p class="mt-3 text-xs text-lh-addon-muted">{{ __('checkout.summary.total_note') }}</p>

    <button type="submit" form="{{ $form }}" data-checkout-submit
        class="mt-6 inline-flex h-14 w-full cursor-pointer items-center justify-center gap-2 rounded-sm bg-lh-addon-accent px-6 text-base font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-wait disabled:opacity-70">
        {{ __('checkout.summary.submit', ['amount' => $money($summary->total)]) }}
        <x-lucide-arrow-right class="size-5 stroke-[1.5]" aria-hidden="true" />
    </button>
    <p class="mt-4 text-xs leading-relaxed text-lh-addon-muted">{{ __('checkout.summary.terms') }}</p>
</section>
