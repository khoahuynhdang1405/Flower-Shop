@props([
    /** @var \App\DTOs\Cart\CartSummaryData $summary */
    'summary',
])

@use('App\Enums\PromotionState')

@php
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $code = $summary->promotionCode;
    $state = $summary->promotionState;
    $row = 'flex items-baseline justify-between gap-4 text-[15px]';
    $darkButton = 'inline-flex items-center justify-center gap-2 rounded-sm bg-lh-addon-accent text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $input = 'h-12 w-full min-w-0 rounded-sm border border-lh-border bg-lh-surface px-4 text-[15px] text-lh-primary-dark placeholder:text-lh-addon-muted focus-visible:border-lh-addon-accent focus-visible:outline-2 focus-visible:-outline-offset-1 focus-visible:outline-lh-addon-accent';
@endphp

{{-- Khối "Gói ghém đơn hoa": mã ưu đãi, tạm tính / ưu đãi / phí giao, tổng dự kiến, nút thanh toán. --}}
<section aria-labelledby="cart-summary-title" {{ $attributes->class('rounded-md bg-lh-addon-selected p-6 sm:p-8') }}>
    <h2 id="cart-summary-title" class="font-serif text-[28px] leading-tight text-lh-primary-dark sm:text-[32px]">{{ __('cart.summary.title') }}</h2>

    {{-- Mã ưu đãi --}}
    <div class="mt-6">
        @if ($code === null)
            <form method="POST" action="{{ route('cart.promotion.store') }}" data-cart-form>
                @csrf
                <label for="promotion-code" class="text-sm font-medium text-lh-primary-dark">{{ __('cart.promotion.label') }}</label>
                <div class="mt-2.5 flex gap-3">
                    <input id="promotion-code" name="code" type="text" value="{{ old('code') }}" maxlength="50" autocomplete="off" autocapitalize="characters"
                        placeholder="{{ __('cart.promotion.placeholder') }}" class="{{ $input }} uppercase placeholder:normal-case"
                        @error('code') aria-invalid="true" aria-describedby="promotion-code-error" @enderror>
                    <button type="submit" class="{{ $darkButton }} h-12 shrink-0 px-5 text-sm">{{ __('cart.promotion.apply') }}</button>
                </div>
                @error('code')
                    <p id="promotion-code-error" class="mt-2.5 text-[13px] text-lh-danger" role="alert">{{ $message }}</p>
                @enderror
            </form>
        @else
            <p class="text-sm font-medium text-lh-primary-dark" id="promotion-code-label">{{ __('cart.promotion.label') }}</p>
            <div class="mt-2.5 flex gap-3">
                <input type="text" value="{{ $code }}" readonly aria-labelledby="promotion-code-label" class="{{ $input }}">
                @if ($state === PromotionState::Applied)
                    <span class="{{ $darkButton }} h-12 shrink-0 px-5 text-sm hover:bg-lh-addon-accent">{{ __('cart.promotion.applied') }}</span>
                @else
                    <form method="POST" action="{{ route('cart.promotion.destroy') }}" class="shrink-0" data-cart-form>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="{{ $darkButton }} h-12 px-5 text-sm">{{ __('cart.promotion.remove') }}</button>
                    </form>
                @endif
            </div>

            <div class="mt-2.5 flex items-start justify-between gap-3 text-[13px]">
                @if ($state === PromotionState::Applied)
                    <p class="flex items-start gap-1.5 text-lh-primary-dark">
                        <x-lucide-check class="mt-0.5 size-3.5 shrink-0 stroke-2" aria-hidden="true" />
                        {{ __('cart.promotion.applied_note', ['amount' => $money($summary->discount)]) }}
                    </p>
                    <form method="POST" action="{{ route('cart.promotion.destroy') }}" class="shrink-0" data-cart-form>
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="{{ __('cart.promotion.remove_label', ['code' => $code]) }}"
                            class="cursor-pointer rounded text-lh-addon-muted underline-offset-4 hover:text-lh-primary-dark hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                            {{ __('cart.promotion.remove') }}
                        </button>
                    </form>
                @elseif ($state === PromotionState::BelowMinimum)
                    <p class="text-lh-addon-muted">{{ __('cart.promotion.below_minimum', [
                        'code' => $code,
                        'amount' => $money($summary->promotionMinOrder),
                        'missing' => $money($summary->promotionMinOrder - $summary->subtotal),
                    ]) }}</p>
                @else
                    <p class="text-lh-danger">{{ __('cart.promotion.unavailable', ['code' => $code]) }}</p>
                @endif
            </div>
        @endif
    </div>

    {{-- Tạm tính, ưu đãi, phí giao --}}
    <dl class="mt-6 space-y-4 border-t border-lh-border pt-6">
        <div class="{{ $row }}">
            <dt class="text-lh-addon-muted">{{ trans_choice('cart.summary.subtotal', $summary->lines->itemCount(), ['count' => $summary->lines->itemCount()]) }}</dt>
            <dd class="text-lh-primary-dark tabular-nums"><x-money :amount="$summary->subtotal" /></dd>
        </div>
        @if ($state === PromotionState::Applied)
            <div class="{{ $row }}">
                <dt class="text-lh-addon-muted">{{ __('cart.summary.discount', ['code' => $code]) }}</dt>
                <dd class="text-lh-primary-dark tabular-nums">−<x-money :amount="$summary->discount" /></dd>
            </div>
        @endif
        <div class="{{ $row }}">
            <dt class="text-lh-addon-muted">{{ __('cart.summary.shipping') }}</dt>
            <dd class="text-right text-lh-primary-dark tabular-nums">
                @if ($summary->shippingFee === null)
                    <span class="text-sm">{{ __('cart.summary.shipping_pending') }}</span>
                @else
                    <x-money :amount="$summary->shippingFee" />
                @endif
            </dd>
        </div>
    </dl>
    <p class="mt-4 max-w-[38ch] text-xs leading-relaxed text-lh-addon-muted">
        @if ($summary->shippingFee === null)
            {{ __('cart.summary.shipping_note_pending', ['missing' => $money($summary->freeShippingFrom - $summary->subtotal)]) }}
        @else
            {{ __('cart.summary.shipping_note_free', ['amount' => $money($summary->freeShippingFrom)]) }}
        @endif
    </p>

    {{-- Tổng dự kiến --}}
    <div class="mt-6 flex items-baseline justify-between gap-4 border-t border-lh-border pt-6">
        <p class="text-base whitespace-nowrap text-lh-primary-dark sm:text-lg">{{ __('cart.summary.total') }}</p>
        <x-money :amount="$summary->total" class="font-serif text-2xl leading-none whitespace-nowrap text-lh-primary-dark lining-nums tabular-nums sm:text-[34px]" />
    </div>

    <a href="{{ route('checkout') }}" class="{{ $darkButton }} mt-6 h-14 w-full px-6 text-base font-medium">
        {{ __('cart.summary.checkout') }}
        <x-lucide-arrow-right class="size-5 stroke-[1.5]" aria-hidden="true" />
    </a>
</section>
