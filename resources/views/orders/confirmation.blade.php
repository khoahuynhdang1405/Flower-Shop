@php
    /**
     * @var \App\Models\Order $order (kèm deliveryDetail, items, promotion)
     *
     * Bố cục theo docs/designs/storefront/07-xac-nhan-don-hang.png. Đơn VNPAY/MoMo chưa trả tiền
     * thì có thêm dòng nhắc + nút thanh toán lại ngay dưới banner.
     */
    use App\Enums\PaymentStatus;

    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20 xl:px-[120px]';
    $delivery = $order->deliveryDetail;
    $date = $delivery->delivery_date->locale(app()->getLocale());
    $method = $order->payment_method->label();
    $isAwaitingPayment = $order->payment_method->isOnline() && $order->payment_status !== PaymentStatus::Paid;
    // Tên gọi: hai chữ cuối của họ tên ("Huỳnh Đăng Khoa" → "Đăng Khoa"); tên trên thiệp: chữ cuối ("Đoan Trang" → "Trang").
    $callName = fn (string $fullName): string => implode(' ', array_slice(preg_split('/\s+/', trim($fullName)), -2));
    $cardName = Str::afterLast(trim($delivery->card_receiver ?: $delivery->receiver_name), ' ');
    $badge = match (true) {
        $order->payment_status === PaymentStatus::Paid => __('order.confirmation.badge.paid', ['method' => $method]),
        ! $order->payment_method->isOnline() => __('order.confirmation.badge.cod'),
        $order->payment_status === PaymentStatus::Failed => __('order.confirmation.badge.failed'),
        default => __('order.confirmation.badge.pending'),
    };
    $paymentNote = match (true) {
        session('payment_error') === true => __('order.confirmation.payment.gateway_error', ['method' => $method]),
        $order->payment_status === PaymentStatus::Failed => __('order.confirmation.payment.failed', ['method' => $method]),
        default => __('order.confirmation.payment.pending', ['method' => $method]),
    };
    $label = 'text-xs uppercase tracking-wide text-lh-addon-muted';
@endphp

<x-layouts.storefront :title="__('order.confirmation.title')">
    <div class="{{ $container }} pt-5 pb-16 lg:pb-24">
        <x-storefront.breadcrumb :items="[
            [__('storefront.menu.home'), url('/')],
            [__('checkout.page.breadcrumb'), null],
            [__('order.confirmation.breadcrumb'), null],
        ]" />

        <div class="mt-8 flex flex-col gap-6 lg:mt-12 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="font-serif text-[40px] leading-tight text-lh-primary-dark sm:text-[46px]">{{ __('order.confirmation.title') }}</h1>
                <p class="mt-3 text-[15px] text-lh-addon-muted sm:text-base">{{ __('order.confirmation.intro', ['name' => $callName($delivery->sender_name)]) }}</p>
            </div>
            <x-storefront.checkout-stepper :current="3" class="lg:mb-3" />
        </div>

        {{-- Banner đặt hoa thành công + trạng thái thanh toán --}}
        <section aria-labelledby="order-success-title" class="mt-10 rounded-md bg-lh-addon-selected px-5 py-6 sm:px-8 sm:py-8 lg:mt-12">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-5 sm:items-center">
                    <span class="grid size-12 shrink-0 place-items-center rounded-full bg-lh-addon-accent text-white sm:size-[62px]" aria-hidden="true">
                        <x-lucide-check class="size-6 stroke-[1.5] sm:size-7" />
                    </span>
                    <div class="min-w-0">
                        <h2 id="order-success-title" class="font-serif text-2xl leading-tight text-lh-primary-dark sm:text-[32px]">
                            {{ __('order.confirmation.success', ['code' => $order->order_code]) }}
                        </h2>
                        <p class="mt-2 text-sm text-lh-addon-muted">{{ __('order.confirmation.success_note') }}</p>
                    </div>
                </div>
                <p @class([
                    'inline-flex shrink-0 items-center gap-1.5 self-start rounded-full bg-lh-surface px-5 py-2.5 text-[13px] sm:self-center',
                    'text-lh-primary-dark' => ! $isAwaitingPayment,
                    'text-lh-danger' => $isAwaitingPayment,
                ])>
                    @if (! $isAwaitingPayment)
                        <x-lucide-check class="size-3.5 stroke-[1.5]" aria-hidden="true" />
                    @endif
                    {{ $badge }}
                </p>
            </div>

            @if ($isAwaitingPayment)
                <div class="mt-6 flex flex-col gap-4 border-t border-lh-border pt-6 sm:flex-row sm:items-center sm:justify-between" role="status">
                    <p class="text-sm text-lh-primary-dark">{{ $paymentNote }}</p>
                    <form method="POST" action="{{ route('orders.payment', $order) }}" class="shrink-0" data-busy-form>
                        @csrf
                        <button type="submit" data-busy-label="{{ __('order.confirmation.payment.retry', ['method' => $method]) }}"
                            class="inline-flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-sm bg-lh-addon-accent px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-wait disabled:opacity-70 sm:w-auto">
                            <x-lucide-qr-code class="size-4 stroke-[1.5]" aria-hidden="true" />
                            <span data-button-label>{{ __('order.confirmation.payment.retry', ['method' => $method]) }}</span>
                        </button>
                    </form>
                </div>
            @endif
        </section>

        <div class="mt-10 grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-12 xl:grid-cols-[minmax(0,1fr)_480px]">
            <div class="space-y-6">
                {{-- Lịch hẹn + người nhận / người đặt --}}
                <section aria-labelledby="schedule-title" class="rounded-md border border-lh-border bg-lh-surface px-5 py-6 sm:px-8 sm:py-8">
                    <h2 id="schedule-title" class="flex items-center gap-3 font-serif text-[26px] leading-tight text-lh-primary-dark sm:text-[28px]">
                        <x-lucide-calendar-check class="size-5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                        {{ __('order.confirmation.schedule.title') }}
                    </h2>

                    <div class="mt-6 flex items-center gap-6 bg-lh-addon-selected px-6 py-5 sm:gap-10 sm:px-8">
                        <p class="text-center text-lh-primary-dark">
                            <span class="block font-serif text-[44px] leading-none tabular-nums">{{ $date->format('d') }}</span>
                            <span class="mt-2 block text-[11px] uppercase text-lh-addon-muted">{{ __('order.confirmation.schedule.month', ['month' => $date->month]) }}</span>
                        </p>
                        <div class="min-w-0 text-lh-primary-dark">
                            <p class="text-base font-medium sm:text-lg">{{ Str::title($date->isoFormat('dddd')) }}, {{ $date->format('d/m/Y') }}</p>
                            <p class="mt-1.5 text-[15px] tabular-nums">{{ __('order.confirmation.schedule.slot', ['slot' => str_replace('-', '–', $delivery->time_slot)]) }}</p>
                        </div>
                    </div>

                    <dl class="mt-8 grid gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="{{ $label }}">{{ __('order.confirmation.receiver') }}</dt>
                            <dd class="mt-2 text-[15px] font-medium text-lh-primary-dark">{{ $delivery->receiver_name }} · <span class="tabular-nums">{{ $delivery->receiver_phone }}</span></dd>
                            <dd class="mt-1.5 text-sm text-lh-addon-muted">{{ $delivery->shipping_address }}, {{ $delivery->shipping_district }}, {{ config('shop.city') }}</dd>
                        </div>
                        <div>
                            <dt class="{{ $label }}">{{ __('order.confirmation.sender') }}</dt>
                            <dd class="mt-2 text-[15px] font-medium text-lh-primary-dark">{{ $delivery->sender_name }}</dd>
                            <dd class="mt-1.5 text-sm text-lh-addon-muted tabular-nums">{{ $delivery->sender_phone }}</dd>
                        </div>
                    </dl>

                    @if ($delivery->delivery_notes)
                        <p class="mt-6 border-t border-lh-border pt-6 text-[13px] text-lh-addon-muted">{{ __('order.confirmation.notes', ['notes' => $delivery->delivery_notes]) }}</p>
                    @endif
                </section>

                {{-- Thiệp (chỉ đơn gửi tặng có lời nhắn) --}}
                @if ($delivery->is_gift && $delivery->card_message)
                    <section aria-labelledby="card-title" class="rounded-md bg-lh-blush-card px-5 py-6 sm:px-8 sm:py-8">
                        <div class="flex items-start justify-between gap-4">
                            <h2 id="card-title" class="font-serif text-[26px] leading-tight text-lh-primary-dark sm:text-[28px]">{{ __('order.confirmation.card.title', ['name' => $cardName]) }}</h2>
                            <x-lucide-mail class="mt-1 size-5 shrink-0 stroke-[1.5] text-lh-primary-dark" aria-hidden="true" />
                        </div>
                        <blockquote class="mt-5 font-serif text-xl leading-relaxed text-lh-primary-dark sm:text-[22px]">“{{ $delivery->card_message }}”</blockquote>
                        @if ($delivery->card_sender || $delivery->card_receiver)
                            <p class="mt-5 text-[13px] text-lh-addon-muted">
                                {{ __('order.confirmation.card.meta', ['sender' => $delivery->card_sender, 'receiver' => $delivery->card_receiver]) }}
                            </p>
                        @endif
                    </section>
                @endif

                <div class="flex items-start gap-4 px-5 py-2 sm:px-8">
                    <x-brand.flower-mark class="mt-0.5 h-5 w-auto shrink-0 text-lh-primary-dark" aria-hidden="true" />
                    <div>
                        <h2 class="text-[15px] font-medium text-lh-primary-dark">{{ __('order.confirmation.next.title') }}</h2>
                        <p class="mt-2 text-[13px] leading-relaxed text-lh-addon-muted">{{ __('order.confirmation.next.body', ['code' => $order->order_code]) }}</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- TODO: trang Tra cứu đơn hàng (08) chưa có route. --}}
                    <a href="#"
                        class="inline-flex h-14 items-center justify-center gap-3 rounded-sm bg-lh-addon-accent px-6 text-base font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        {{ __('order.confirmation.track') }}
                        <x-lucide-arrow-right class="size-5 stroke-[1.5]" aria-hidden="true" />
                    </a>
                    <a href="{{ url('/') }}"
                        class="inline-flex h-14 items-center justify-center rounded-sm border border-lh-primary-dark px-6 text-base text-lh-primary-dark transition-colors hover:bg-lh-addon-selected focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        {{ __('order.confirmation.home') }}
                    </a>
                </div>
            </div>

            <div class="space-y-10 lg:sticky lg:top-8">
                <x-storefront.order-summary :order="$order" />

                <section aria-labelledby="help-title" class="px-6">
                    <h2 id="help-title" class="font-serif text-2xl leading-tight text-lh-primary-dark">{{ __('order.confirmation.help.title') }}</h2>
                    <p class="mt-2 text-[13px] leading-relaxed text-lh-addon-muted">
                        {{ __('order.confirmation.help.body', ['hotline' => config('shop.hotline'), 'code' => $order->order_code]) }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-layouts.storefront>
