@props([
    // Route PATCH đổi số lượng của dòng.
    'action',
    'quantity',
    // Tên món, cho nhãn đọc màn hình.
    'name',
])

@php
    $max = \App\Actions\Cart\AddFlowerToCartAction::MAX_QUANTITY;
    $stepButton = 'grid h-full w-9 place-items-center text-lh-primary-dark transition-colors hover:bg-lh-addon-selected focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-not-allowed disabled:text-lh-border disabled:hover:bg-transparent';
@endphp

{{-- Bộ − số lượng + của một dòng giỏ: mỗi nút gửi thẳng số lượng mới (chạy được cả khi tắt JavaScript). --}}
{{-- data-step / data-quantity-*: resources/js/cart.js đổi số ngay khi bấm, trước khi server trả về. --}}
<form method="POST" action="{{ $action }}" data-cart-form data-max="{{ $max }}" {{ $attributes->class('inline-flex h-10 items-stretch rounded-sm border border-lh-border bg-lh-surface') }}>
    @csrf
    @method('PATCH')
    <button type="submit" name="quantity" value="{{ $quantity - 1 }}" data-step="-1" class="{{ $stepButton }}" @disabled($quantity <= 1)
        aria-label="{{ __('cart.line.decrease', ['name' => $name]) }}">
        <x-lucide-minus class="size-4 stroke-[1.5]" aria-hidden="true" />
    </button>
    <span class="grid min-w-8 place-items-center text-sm tabular-nums">
        <span aria-hidden="true" data-quantity-value>{{ $quantity }}</span>
        <span class="sr-only" data-quantity-label data-template="{{ __('cart.line.quantity_label', ['name' => $name, 'quantity' => ':quantity']) }}">{{ __('cart.line.quantity_label', ['name' => $name, 'quantity' => $quantity]) }}</span>
    </span>
    <button type="submit" name="quantity" value="{{ $quantity + 1 }}" data-step="1" class="{{ $stepButton }}" @disabled($quantity >= $max)
        aria-label="{{ __('cart.line.increase', ['name' => $name]) }}">
        <x-lucide-plus class="size-4 stroke-[1.5]" aria-hidden="true" />
    </button>
</form>
