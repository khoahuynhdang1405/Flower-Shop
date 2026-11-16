@php
    /**
     * @var \App\Models\Order|null $order (kèm deliveryDetail, items, statusLogs mới nhất trước)
     * @var bool $searched
     *
     * Bố cục theo docs/designs/storefront/08-tra-cuu-don-hang.png.
     */
    use App\Enums\CartItemType;
    use App\Enums\OrderStatus;
    use App\Enums\PaymentMethod;
    use App\Enums\PaymentStatus;

    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20 xl:px-[120px]';
    $card = 'rounded-md border border-lh-border bg-lh-surface px-5 py-6 sm:px-8 sm:py-8';
    $sectionTitle = 'font-serif text-[26px] leading-tight text-lh-primary-dark sm:text-[28px]';
    $input = 'mt-2 h-12 w-full rounded-sm border bg-lh-surface px-4 text-[15px] text-lh-primary-dark placeholder:text-lh-muted focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary';
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');

    if ($order) {
        $delivery = $order->deliveryDetail;
        $status = $order->order_status;
        $currentStep = $status->trackingStep();
        $logs = $order->statusLogs;
        $latestLog = $logs->first();
        $slot = str_replace('-', '–', $delivery->time_slot);
        $deliveryDate = $delivery->delivery_date->locale(app()->getLocale());
        $receiverName = $delivery->card_receiver ?: $delivery->receiver_name;
        $flowers = $order->items->where('item_type', CartItemType::Flower);
        $addons = $order->items->where('item_type', CartItemType::Addon);
        $method = $order->payment_method->label();
        $amount = $money($order->total_amount);
        $paymentLine = match (true) {
            $order->payment_status === PaymentStatus::Paid => __('order.tracking.paid', ['amount' => $amount, 'method' => $method]),
            $order->payment_method === PaymentMethod::Cod => __('order.tracking.cod', ['amount' => $amount]),
            default => __('order.tracking.unpaid', ['status' => $order->payment_status->label(), 'amount' => $amount, 'method' => $method]),
        };
        // Timeline 4 bước; thời điểm mỗi bước = log sớm nhất thuộc bước đó.
        $steps = [
            'received' => ['statuses' => [OrderStatus::Received], 'icon' => 'lucide-check'],
            'preparing' => ['statuses' => [OrderStatus::Preparing, OrderStatus::PreparedReady], 'icon' => 'brand.flower-mark'],
            'shipping' => ['statuses' => [OrderStatus::Shipping], 'icon' => 'lucide-truck'],
            'completed' => ['statuses' => [OrderStatus::Completed], 'icon' => 'lucide-package'],
        ];
        $stepTime = fn (array $statuses) => $logs->filter(fn ($log) => in_array($log->status, $statuses, true))->last()?->created_at;
    }
@endphp

<x-layouts.storefront :title="__('order.tracking.breadcrumb')">
    <div class="{{ $container }} pt-5 pb-16 lg:pb-24">
        <x-storefront.breadcrumb :items="[
            [__('storefront.menu.home'), url('/')],
            [__('order.tracking.breadcrumb'), null],
        ]" />

        <h1 class="mt-8 font-serif text-[40px] leading-tight text-lh-primary-dark sm:text-[46px] lg:mt-12">{{ __('order.tracking.title') }}</h1>
        <p class="mt-3 text-[15px] text-lh-addon-muted sm:text-base">{{ __('order.tracking.intro') }}</p>

        {{-- Form tra cứu --}}
        <form method="GET" action="{{ route('orders.track') }}" aria-label="{{ __('order.tracking.form.label') }}" novalidate
            class="mt-10 grid gap-5 rounded-md bg-lh-sage-50 px-5 py-6 sm:px-7 sm:py-8 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-start">
            @foreach (['code' => ['autocomplete' => 'off', 'inputmode' => 'text'], 'phone' => ['autocomplete' => 'tel', 'inputmode' => 'tel']] as $field => $inputAttrs)
                <div>
                    <label for="track-{{ $field }}" class="text-[13px] font-medium text-lh-primary-dark">{{ __('order.tracking.form.'.$field) }}</label>
                    <input id="track-{{ $field }}" name="{{ $field }}" type="{{ $field === 'phone' ? 'tel' : 'text' }}" required
                        value="{{ old($field, request()->query($field)) }}" placeholder="{{ __('order.tracking.form.'.$field.'_placeholder') }}"
                        autocomplete="{{ $inputAttrs['autocomplete'] }}" inputmode="{{ $inputAttrs['inputmode'] }}"
                        @error($field) aria-invalid="true" aria-describedby="track-{{ $field }}-error" @enderror
                        @class([$input, 'border-lh-border' => ! $errors->has($field), 'border-lh-danger' => $errors->has($field)])>
                    @error($field)
                        <p id="track-{{ $field }}-error" class="mt-2 text-[13px] text-lh-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
            <button type="submit"
                class="inline-flex h-12 cursor-pointer items-center justify-center gap-3 rounded-sm bg-lh-primary-dark px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary md:mt-[26px]">
                {{ __('order.tracking.form.submit') }}
                <x-lucide-search class="size-[18px] stroke-[1.5]" aria-hidden="true" />
            </button>
        </form>

        @if ($searched && ! $order)
            <p role="status" class="mt-8 flex items-start gap-3 rounded-md border border-lh-border bg-lh-surface px-5 py-5 text-sm text-lh-primary-dark sm:px-7">
                <x-lucide-leaf class="mt-0.5 size-5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                {{ __('order.tracking.not_found') }}
            </p>
        @endif

        @if ($order)
            {{-- Hành trình đơn hoa --}}
            <section aria-labelledby="journey-title" class="{{ $card }} mt-8 lg:px-10 lg:py-10">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 id="journey-title" class="font-serif text-[28px] leading-tight text-lh-primary-dark sm:text-[32px]">
                            {{ __('order.tracking.journey', ['code' => $order->order_code]) }}
                        </h2>
                        <p class="mt-2 text-[13px] text-lh-addon-muted">
                            {{ __('order.tracking.placed_on', ['date' => $order->created_at->format('d/m/Y')]) }} · {{ $paymentLine }}
                        </p>
                    </div>
                    <p @class([
                        'shrink-0 self-start rounded-full px-4 py-2 text-[13px]',
                        'bg-lh-canvas text-lh-muted' => $status === OrderStatus::Cancelled,
                        'bg-lh-sage-50 text-lh-primary-dark' => $status !== OrderStatus::Cancelled,
                    ])>{{ $status->customerLabel() }}</p>
                </div>

                @if ($currentStep !== null)
                    <ol class="mt-10 grid gap-6 sm:grid-cols-4 sm:gap-0">
                        @foreach ($steps as $key => $step)
                            @php
                                $isDone = $loop->index < $currentStep;
                                $isCurrent = $loop->index === $currentStep;
                                $time = $stepTime($step['statuses']);
                            @endphp
                            <li @if ($isCurrent) aria-current="step" @endif
                                @class([
                                    'relative flex items-center gap-4 sm:flex-col sm:gap-0 sm:text-center',
                                    // Đường nối từ bước trước tới bước này (chỉ khi xếp ngang).
                                    'sm:before:absolute sm:before:top-[26px] sm:before:right-[calc(50%+26px)] sm:before:left-[calc(-50%+26px)] sm:before:h-0.5' => ! $loop->first,
                                    'sm:before:bg-lh-primary-dark' => ! $loop->first && $loop->index <= $currentStep,
                                    'sm:before:bg-lh-border' => ! $loop->first && $loop->index > $currentStep,
                                ])>
                                <span @class([
                                    'grid size-[52px] shrink-0 place-items-center rounded-full border',
                                    'border-lh-primary-dark bg-lh-addon-selected text-lh-primary-dark' => $isDone,
                                    'border-lh-primary-dark bg-lh-primary-dark text-white' => $isCurrent,
                                    'border-lh-border bg-lh-canvas text-lh-muted' => ! $isDone && ! $isCurrent,
                                ]) aria-hidden="true">
                                    <x-dynamic-component :component="$isDone ? 'lucide-check' : $step['icon']" class="h-5 w-auto stroke-[1.5]" />
                                </span>
                                <span class="min-w-0 sm:mt-4">
                                    <span @class([
                                        'block text-[15px]',
                                        'font-semibold text-lh-primary-dark' => $isCurrent,
                                        'text-lh-primary-dark' => $isDone,
                                        'text-lh-muted' => ! $isDone && ! $isCurrent,
                                    ])>{{ __('order.tracking.steps.'.$key) }}</span>
                                    <span class="mt-1.5 block text-xs text-lh-addon-muted tabular-nums">
                                        {{ $time ? $time->format('d/m · H:i') : __('order.tracking.waiting.'.$key) }}
                                    </span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                @endif

                <div class="mt-8 flex items-start gap-3 rounded-sm bg-lh-sage-50 px-5 py-5 sm:px-6" role="status">
                    <x-lucide-leaf class="mt-0.5 size-5 shrink-0 stroke-[1.5] text-lh-primary-dark" aria-hidden="true" />
                    <div class="min-w-0">
                        <p class="text-[15px] text-lh-primary-dark">
                            {{ __('order.tracking.messages.'.$status->value, ['name' => $receiverName, 'hotline' => config('shop.hotline')]) }}
                        </p>
                        @if ($status !== OrderStatus::Cancelled)
                            <p class="mt-1.5 text-[13px] text-lh-addon-muted">
                                @if ($latestLog)
                                    {{ __('order.tracking.updated_at', ['time' => $latestLog->created_at->format('H:i'), 'date' => $latestLog->created_at->format('d/m/Y')]) }}
                                @endif
                                @if ($status !== OrderStatus::Completed)
                                    {{ $delivery->delivery_date->isToday()
                                        ? __('order.tracking.delivery_today', ['slot' => $slot])
                                        : __('order.tracking.delivery_on', ['date' => $delivery->delivery_date->format('d/m/Y'), 'slot' => $slot]) }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            <div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_400px] xl:grid-cols-[minmax(0,1fr)_490px]">
                <div class="space-y-12">
                    {{-- Lịch hẹn giao hoa --}}
                    <section aria-labelledby="schedule-title" class="{{ $card }}">
                        <h2 id="schedule-title" class="flex items-center gap-3 {{ $sectionTitle }}">
                            <x-lucide-calendar-days class="size-5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                            {{ __('order.tracking.schedule_title') }}
                        </h2>
                        <p class="mt-6 text-lg font-medium text-lh-primary-dark tabular-nums sm:text-xl">
                            {{ Str::title($deliveryDate->isoFormat('dddd')) }}, {{ $deliveryDate->format('d/m/Y') }} · {{ $slot }}
                        </p>
                        <p class="mt-5 text-sm text-lh-primary-dark">{{ $delivery->receiver_name }} · <span class="tabular-nums">{{ $delivery->receiver_phone }}</span></p>
                        <p class="mt-3 text-sm text-lh-addon-muted">{{ $delivery->shipping_address }}, {{ $delivery->shipping_district }}, {{ config('shop.city') }}</p>
                        @if ($delivery->delivery_notes)
                            <p class="mt-6 border-t border-lh-border pt-6 text-[13px] text-lh-addon-muted">{{ __('order.tracking.notes', ['notes' => $delivery->delivery_notes]) }}</p>
                        @endif
                    </section>

                    {{-- Những cập nhật từ tiệm --}}
                    @if ($logs->isNotEmpty())
                        <section aria-labelledby="updates-title" class="px-1 sm:px-8">
                            <h2 id="updates-title" class="{{ $sectionTitle }}">{{ __('order.tracking.updates_title') }}</h2>
                            <ol class="mt-6 space-y-6">
                                @foreach ($logs as $log)
                                    <li class="grid gap-1 sm:grid-cols-[9rem_minmax(0,1fr)] sm:gap-6">
                                        <time datetime="{{ $log->created_at->toIso8601String() }}" class="text-xs text-lh-addon-muted tabular-nums sm:pt-0.5">
                                            {{ $log->created_at->format('d/m · H:i') }}
                                        </time>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-lh-primary-dark">{{ $log->title }}</p>
                                            @if ($log->description)
                                                <p class="mt-1.5 text-[13px] text-lh-addon-muted">{{ $log->description }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif
                </div>

                {{-- Món quà bạn đã gửi --}}
                <section aria-labelledby="gift-title" class="rounded-md bg-lh-blush-card px-5 py-6 sm:px-8 sm:py-8">
                    <h2 id="gift-title" class="{{ $sectionTitle }}">{{ __('order.tracking.gift_title') }}</h2>

                    <ul class="mt-6 space-y-5">
                        @foreach ($flowers as $flower)
                            <li class="flex gap-5">
                                @if ($flower->product?->primaryImage)
                                    <img src="{{ $flower->product->primaryImage->url }}" alt="" loading="lazy" class="aspect-[6/7] w-[120px] shrink-0 rounded-sm bg-lh-sage-100 object-cover">
                                @else
                                    <x-flower-image :name="$flower->product->slug ?? ''" class="aspect-[6/7] w-[120px] shrink-0 rounded-sm" />
                                @endif
                                <div class="min-w-0 pt-1">
                                    <h3 class="font-serif text-2xl leading-tight text-lh-primary-dark">{{ $flower->item_name }}</h3>
                                    <p class="mt-2 text-xs text-lh-addon-muted">
                                        {{ $flower->size_name
                                            ? __('checkout.summary.flower_meta', ['size' => Str::lower($flower->size_name), 'quantity' => $flower->quantity])
                                            : __('checkout.summary.flower_meta_no_size', ['quantity' => $flower->quantity]) }}
                                    </p>
                                    @if ($loop->first && $addons->isNotEmpty())
                                        <p class="mt-2 text-[13px] text-lh-primary-dark">{{ __('order.tracking.with_addons', ['names' => $addons->pluck('item_name')->join(', ', ' & ')]) }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @if ($delivery->is_gift && $delivery->card_message)
                        <div class="mt-6 border-t border-lh-border pt-6">
                            @if ($delivery->card_sender || $delivery->card_receiver)
                                <p class="text-[11px] tracking-wide text-lh-addon-muted uppercase">
                                    {{ __('order.tracking.card_meta', ['sender' => $delivery->card_sender, 'receiver' => $delivery->card_receiver]) }}
                                </p>
                            @endif
                            <blockquote class="mt-5 font-serif text-xl leading-relaxed text-lh-primary-dark">“{{ $delivery->card_message }}”</blockquote>
                        </div>
                    @endif

                    <div class="mt-6 border-t border-lh-border pt-6">
                        <p class="text-[13px] text-lh-addon-muted">{{ __('order.tracking.help', ['hotline' => config('shop.hotline')]) }}</p>
                        <a href="tel:{{ preg_replace('/\s+/', '', config('shop.hotline')) }}"
                            class="mt-5 inline-flex h-12 w-full items-center justify-center gap-3 rounded-sm border border-lh-primary-dark bg-lh-surface px-6 text-[15px] text-lh-primary-dark transition-colors hover:bg-lh-addon-selected focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                            {{ __('order.tracking.contact') }}
                            <x-lucide-phone class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                        </a>
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-layouts.storefront>
