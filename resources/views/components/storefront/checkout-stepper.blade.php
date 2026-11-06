@props([
    // Bước hiện tại: 1 = Giỏ hàng, 2 = Thanh toán, 3 = Hoàn tất.
    'current' => 1,
])

@php
    $steps = [__('cart.steps.cart'), __('cart.steps.checkout'), __('cart.steps.done')];
@endphp

{{-- Stepper 3 bước Giỏ hàng → Thanh toán → Hoàn tất (giỏ hàng, thanh toán, xác nhận đơn). --}}
<nav aria-label="{{ __('cart.steps.label') }}" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-x-4 gap-y-2 text-[13px] sm:gap-x-6 sm:text-sm">
        @foreach ($steps as $label)
            @php($isCurrent = $loop->iteration === $current)
            <li class="flex items-center gap-2 whitespace-nowrap sm:gap-2.5"@if ($isCurrent) aria-current="step" @endif>
                <span @class([
                    'grid size-7 place-items-center rounded-full text-xs tabular-nums',
                    'bg-lh-addon-accent text-white' => $isCurrent,
                    'bg-lh-sage-50 text-lh-addon-muted' => ! $isCurrent,
                ]) aria-hidden="true">{{ $loop->iteration }}</span>
                <span @class(['text-lh-primary-dark' => $isCurrent, 'text-lh-addon-muted' => ! $isCurrent])>{{ $label }}</span>
            </li>
        @endforeach
    </ol>
</nav>
