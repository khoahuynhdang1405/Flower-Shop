@php
    /**
     * @var \App\DTOs\Cart\CartSummaryData $summary
     * @var \App\Models\User|null $customer
     * @var \Illuminate\Support\Carbon $today
     *
     * Bảng màu trang (Figma): 2F5142 → lh-addon-accent, 737C72 → lh-addon-muted, EDF1E8 → lh-addon-selected,
     * F2E6DE → lh-blush-card; 263F34 / DCE1D6 / FAF8F3 lệch 1 đơn vị so với lh-primary-dark / lh-border / lh-cream nên dùng token sẵn có.
     * Tên ô nhập trùng tên cột order_delivery_details / orders.payment_method để nối backend sau.
     */
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20 xl:px-[120px]';
    $phonePattern = '(0|\+84)[\s.\-]?[35789]([\s.\-]?[0-9]){8}';
    $isGift = old('is_gift', '1') === '1';
    $timeSlot = old('time_slot');
    $paymentMethod = old('payment_method', 'vnpay');
    $paymentMethods = ['cod', 'vnpay', 'momo'];
@endphp

<x-layouts.storefront :title="__('checkout.page.title')">
    <div class="{{ $container }} pt-5 pb-16 lg:pb-24" data-checkout>
        <x-storefront.breadcrumb :items="[
            [__('storefront.menu.home'), url('/')],
            [__('cart.page.breadcrumb'), route('cart.show')],
            [__('checkout.page.breadcrumb'), null],
        ]" />

        <div class="mt-8 flex flex-col gap-6 lg:mt-12 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="font-serif text-[40px] leading-tight text-lh-primary-dark sm:text-[46px]">{{ __('checkout.page.title') }}</h1>
                <p class="mt-3 text-[15px] text-lh-addon-muted sm:text-base">{{ __('checkout.page.intro') }}</p>
            </div>
            <x-storefront.checkout-stepper :current="2" class="lg:mb-12" />
        </div>

        <div class="mt-10 grid items-start gap-10 lg:mt-12 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-12 xl:grid-cols-[minmax(0,1fr)_445px] xl:gap-14">
            {{-- TODO: chưa có route lưu đơn — resources/js/checkout.js chặn gửi, chỉ kiểm tra dữ liệu và báo cho khách. --}}
            <form id="checkout-form" method="POST" action="#" novalidate class="space-y-6" data-checkout-form>
                @csrf

                {{-- 01 · Người đặt --}}
                <x-storefront.checkout-section id="sender" :number="1" :title="__('checkout.sender.title')">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-form.input name="sender_name" :label="__('checkout.sender.name')" :value="$customer?->name"
                            :placeholder="__('checkout.sender.name_placeholder')" autocomplete="name" required maxlength="255" />
                        <x-form.input name="sender_phone" type="tel" :label="__('checkout.sender.phone')" :value="$customer?->phone"
                            placeholder="0903 456 789" autocomplete="tel" inputmode="tel" required pattern="{{ $phonePattern }}" />
                        <div class="md:col-span-2">
                            <x-form.input name="sender_email" type="email" :label="__('checkout.sender.email')" :value="$customer?->email"
                                :placeholder="__('checkout.sender.email_placeholder')" autocomplete="email" required maxlength="255" />
                        </div>
                    </div>

                    <fieldset class="mt-5">
                        <legend class="sr-only">{{ __('checkout.sender.recipient_type') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <x-storefront.choice-card name="is_gift" value="1" :label="__('checkout.sender.gift')" :checked="$isGift" />
                            <x-storefront.choice-card name="is_gift" value="0" :label="__('checkout.sender.self')" :checked="! $isGift" />
                        </div>
                    </fieldset>
                </x-storefront.checkout-section>

                {{-- 02 · Người nhận --}}
                <x-storefront.checkout-section id="receiver" :number="2" :title="__('checkout.receiver.title')" :description="__('checkout.receiver.description')">
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-form.input name="receiver_name" :label="__('checkout.receiver.name')"
                            autocomplete="off" required maxlength="255"
                            class="read-only:bg-lh-canvas read-only:text-lh-addon-muted" />
                        <x-form.input name="receiver_phone" type="tel" :label="__('checkout.receiver.phone')"
                            autocomplete="off" inputmode="tel" required pattern="{{ $phonePattern }}"
                            class="read-only:bg-lh-canvas read-only:text-lh-addon-muted" />
                        <div class="md:col-span-2">
                            <x-form.input name="shipping_address" :label="__('checkout.receiver.address')"
                                :placeholder="__('checkout.receiver.address_placeholder')" autocomplete="street-address" required maxlength="500" />
                        </div>
                        <x-form.select name="city" :label="__('checkout.receiver.city')" :options="[config('shop.city')]"
                            :value="config('shop.city')" :hint="__('checkout.receiver.city_hint')" disabled
                            class="disabled:bg-lh-canvas disabled:text-lh-primary-dark" />
                        <x-form.select name="shipping_district" :label="__('checkout.receiver.district')" :options="config('shop.districts')"
                            :placeholder="__('checkout.receiver.district_placeholder')" autocomplete="address-level2" required />
                        <div class="md:col-span-2">
                            <x-form.input name="delivery_notes" :label="__('checkout.receiver.notes')"
                                :placeholder="__('checkout.receiver.notes_placeholder')" maxlength="500" />
                        </div>
                    </div>
                </x-storefront.checkout-section>

                {{-- 03 · Ngày + khung giờ --}}
                <x-storefront.checkout-section id="schedule" :number="3" :title="__('checkout.schedule.title')">
                    <x-form.date-picker name="delivery_date" :label="__('checkout.schedule.date')" required
                        :placeholder="__('checkout.schedule.date_placeholder')" :required-message="__('checkout.schedule.date_required')"
                        min="{{ $today->format('Y-m-d') }}" max="{{ $today->copy()->addDays(config('shop.delivery_max_days_ahead'))->format('Y-m-d') }}" />

                    {{-- Khung giờ của hôm nay đã bắt đầu thì resources/js/checkout.js khóa lại (theo giờ của tiệm, data-timezone). --}}
                    <fieldset class="mt-5" aria-describedby="time-slot-past-note time-slot-note" data-time-slots data-timezone="{{ config('app.timezone') }}">
                        <legend class="mb-2 block text-sm font-medium text-lh-heading">{{ __('checkout.schedule.time_slot') }}</legend>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach (config('shop.delivery_time_slots') as $slot)
                                <label class="flex h-12 cursor-pointer items-center justify-center rounded-sm border border-lh-border bg-lh-surface text-sm text-lh-primary-dark tabular-nums transition-colors hover:border-lh-addon-muted has-checked:border-lh-addon-accent has-checked:bg-lh-addon-accent has-checked:text-white has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-lh-primary has-disabled:cursor-not-allowed has-disabled:border-lh-border has-disabled:bg-lh-canvas has-disabled:text-lh-muted has-disabled:line-through">
                                    <input type="radio" name="time_slot" value="{{ $slot }}" class="sr-only" @checked($timeSlot === $slot) required>
                                    {{ str_replace('-', '–', $slot) }}
                                </label>
                            @endforeach
                        </div>
                        <p id="time-slot-past-note" class="mt-3 text-xs text-lh-addon-muted" hidden data-time-slots-past-note>{{ __('checkout.schedule.past_slots_note') }}</p>
                        @error('time_slot')
                            <p class="mt-2 flex items-start gap-1.5 text-xs text-lh-danger">
                                <x-lucide-circle-alert class="mt-px size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                                {{ $message }}
                            </p>
                        @enderror
                        <p id="time-slot-note" class="mt-3 text-xs text-lh-addon-muted">{{ __('checkout.schedule.note') }}</p>
                    </fieldset>
                </x-storefront.checkout-section>

                {{-- 04 · Thiệp (ẩn khi khách tự nhận hoa) --}}
                <x-storefront.checkout-section id="card" :number="4" :title="__('checkout.card.title')" :description="__('checkout.card.description')"
                    blush :hidden="! $isGift" data-gift-only>
                    <div class="grid gap-5 md:grid-cols-2">
                        <x-form.input name="card_sender" :label="__('checkout.card.sender')" maxlength="100" :disabled="! $isGift" />
                        <x-form.input name="card_receiver" :label="__('checkout.card.receiver')" maxlength="100" :disabled="! $isGift" />
                        <div class="md:col-span-2">
                            <x-form.textarea name="card_message" :label="__('checkout.card.message')" :hint="__('checkout.card.note')"
                                :placeholder="__('checkout.card.message_placeholder')" rows="3" maxlength="500" counter :disabled="! $isGift"
                                class="field-sizing-content min-h-[86px] max-h-[186px] resize-y supports-[field-sizing:content]:resize-none" />
                        </div>
                    </div>
                </x-storefront.checkout-section>

                {{-- 05 · Thanh toán --}}
                <x-storefront.checkout-section id="payment" :number="5" :title="__('checkout.payment.title')">
                    <fieldset>
                        <legend class="sr-only">{{ __('checkout.payment.title') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach ($paymentMethods as $method)
                                <x-storefront.choice-card name="payment_method" :value="$method"
                                    :label="__('checkout.payment.methods.'.$method.'.label')"
                                    :hint="__('checkout.payment.methods.'.$method.'.hint')"
                                    :checked="$paymentMethod === $method" />
                            @endforeach
                        </div>
                    </fieldset>
                    <p class="mt-4 flex items-start gap-2 text-xs text-lh-addon-muted">
                        <x-lucide-lock class="mt-px size-3.5 shrink-0 stroke-[1.5] text-lh-primary-dark" aria-hidden="true" />
                        <span data-payment-note
                            @foreach ($paymentMethods as $method) data-note-{{ $method }}="{{ __('checkout.payment.methods.'.$method.'.note') }}" @endforeach
                        >{{ __('checkout.payment.methods.'.$paymentMethod.'.note') }}</span>
                    </p>
                </x-storefront.checkout-section>

                <a href="{{ route('cart.show') }}"
                    class="inline-flex items-center gap-3 rounded text-[15px] text-lh-primary-dark underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lh-primary">
                    <x-lucide-arrow-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                    {{ __('checkout.page.back') }}
                </a>
            </form>

            <div class="space-y-6 lg:sticky lg:top-8">
                <x-storefront.checkout-summary :summary="$summary" form="checkout-form" />

                <section aria-labelledby="appointment-title" class="flex items-start gap-4 rounded-md border border-lh-border bg-lh-surface px-6 py-5">
                    <x-lucide-calendar-check class="mt-0.5 size-5 shrink-0 stroke-[1.5] text-lh-primary-dark" aria-hidden="true" />
                    <div class="min-w-0 text-sm">
                        <h2 id="appointment-title" class="font-medium text-lh-primary-dark">{{ __('checkout.appointment.title') }}</h2>
                        <p class="mt-1.5 text-[13px] text-lh-addon-muted tabular-nums" data-appointment-when
                            data-empty="{{ __('checkout.appointment.empty') }}">{{ __('checkout.appointment.empty') }}</p>
                        <p class="mt-1 text-[13px] text-lh-primary-dark empty:hidden" data-appointment-who></p>
                    </div>
                </section>

                <section aria-labelledby="help-title" class="px-6">
                    <h2 id="help-title" class="font-serif text-2xl leading-tight text-lh-primary-dark">{{ __('checkout.help.title') }}</h2>
                    <p class="mt-2 text-[13px] leading-relaxed text-lh-addon-muted">
                        {{ __('checkout.help.body', ['hotline' => config('shop.hotline'), 'hours' => config('shop.opening_hours')]) }}
                    </p>
                </section>
            </div>
        </div>
    </div>
</x-layouts.storefront>
