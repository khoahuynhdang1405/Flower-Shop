@php
    /*
     * TODO: đơn hàng vẫn là dữ liệu mẫu theo docs/designs/storefront/13-tai-khoan.png — thay bằng orders của khách
     * khi làm phần đơn hàng.
     */
    $orders = [
        ['code' => '#LH1028', 'name' => 'Lời thì thầm', 'image' => 'loi-thi-tham', 'date' => '07/10/2026', 'items' => 1, 'total' => 690000, 'status' => 'preparing', 'delivery_time' => '10:30'],
        ['code' => '#LH0986', 'name' => 'Nắng trong veo', 'image' => 'nang-trong-veo', 'date' => '28/09/2026', 'items' => 1, 'total' => 550000, 'status' => 'completed'],
        ['code' => '#LH0932', 'name' => 'Bình yên xanh', 'image' => 'binh-yen-xanh', 'date' => '15/09/2026', 'items' => 1, 'total' => 750000, 'status' => 'completed'],
        ['code' => '#LH0881', 'name' => 'Một ngày bình dị', 'image' => 'mot-ngay-binh-di', 'date' => '02/09/2026', 'items' => 1, 'total' => 590000, 'status' => 'completed'],
    ];

    $preparingCount = collect($orders)->where('status', 'preparing')->count();
    $completedCount = collect($orders)->where('status', 'completed')->count();
    $latestPreparing = collect($orders)->firstWhere('status', 'preparing');

    $hasPassword = filled($user->password);

    $chip = 'rounded-full bg-lh-canvas px-3 py-1.5 text-xs text-lh-muted transition-colors hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-lh-primary aria-pressed:bg-lh-sage-100 aria-pressed:text-lh-heading';
    $primaryButton = 'inline-flex h-11 items-center justify-center rounded bg-lh-primary px-5 text-sm font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-wait disabled:opacity-80';
    $savedLine = 'flex items-center gap-2 text-xs';
@endphp

<x-layouts.storefront :title="__('account.title')">
    <div class="mx-auto max-w-[1440px] px-4 pt-8 pb-16 sm:px-8 lg:px-20 lg:pt-10 lg:pb-20">
        <x-storefront.breadcrumb :items="[[__('storefront.menu.home'), url('/')], [__('account.title'), null]]" />

        <h1 class="mt-5 font-serif text-[34px] leading-tight font-normal [overflow-wrap:anywhere] sm:text-[44px]">
            {{ __('account.greeting', ['name' => $customer['short_name']]) }}
        </h1>
        <p class="mt-4 text-[15px] text-lh-muted">{{ __('account.subtitle') }}</p>

        <div class="mt-10 grid items-start gap-8 lg:grid-cols-[252px_minmax(0,1fr)] lg:gap-10">
            <x-account.sidebar :customer="$customer" />

            <div class="flex flex-col gap-8">
                {{-- Thông tin tài khoản + địa chỉ giao hoa mặc định --}}
                <x-account.section id="profile" :title="__('account.profile.title')" :description="__('account.profile.description')">
                    @if ($user->google_id)
                        <x-slot:badge>
                            <span class="shrink-0 rounded-full bg-lh-sage-50 px-3 py-1 text-xs">{{ __('account.profile.verified') }}</span>
                        </x-slot:badge>
                    @endif

                    <form method="POST" action="{{ route('account.profile.update') }}" novalidate data-busy-form class="px-5 pt-6 pb-6 sm:px-7 sm:pb-7">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-5 md:grid-cols-2">
                            <x-form.input name="name" :label="__('account.profile.name')" marker validate
                                :value="$user->name" autocomplete="name" required minlength="2" maxlength="255" />
                            <x-form.input name="phone" type="tel" :label="__('account.profile.phone')" marker validate
                                :value="$user->phone" autocomplete="tel" inputmode="tel" required
                                pattern="(0|\+84)[\s.\-]?[35789]([\s.\-]?[0-9]){8}" />
                            <x-form.input name="email" type="email" :label="__('account.profile.email')"
                                :value="$user->email" :hint="__('account.profile.email_hint')" readonly
                                class="read-only:bg-lh-canvas read-only:text-lh-muted" />
                            <x-form.input name="birthday" type="date" :label="__('account.profile.birthday')"
                                :value="$user->birthday?->format('Y-m-d')" autocomplete="bday"
                                min="1900-01-02" max="{{ now()->subDay()->format('Y-m-d') }}" />
                        </div>

                        <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="{{ $savedLine }} text-lh-muted" role="status">
                                <x-lucide-circle-check class="size-4 shrink-0 stroke-[1.5] text-lh-heading" aria-hidden="true" />
                                @if (session('status') === 'profile-updated')
                                    <span class="text-lh-heading">{{ __('account.profile.saved') }}</span>
                                @else
                                    {{ __('account.profile.saved_at', ['time' => $customer['saved_at']]) }}
                                @endif
                            </p>
                            <button type="submit" class="{{ $primaryButton }}" data-busy-label="{{ __('auth.busy') }}">
                                <span data-button-label>{{ __('account.profile.submit') }}</span>
                            </button>
                        </div>
                    </form>

                    <form id="address" method="POST" action="{{ route('account.address.update') }}" novalidate data-busy-form
                        aria-labelledby="address-title" class="scroll-mt-6 border-t border-lh-border px-5 pt-6 pb-6 sm:px-7 sm:pb-7">
                        @csrf
                        @method('PATCH')

                        <h3 id="address-title" class="text-[15px] font-semibold">{{ __('account.address.title') }}</h3>
                        <p class="mt-1.5 text-[13px] text-lh-muted">{{ __('account.address.description') }}</p>

                        <div class="mt-5 grid gap-5 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                            <x-form.input name="default_shipping_address" :label="__('account.address.street')" marker validate
                                :value="$user->default_shipping_address" :placeholder="__('account.address.street_placeholder')"
                                autocomplete="street-address" required maxlength="500" />
                            <x-form.select name="default_shipping_district" :label="__('account.address.district')" marker
                                :options="config('shop.districts')" :value="$user->default_shipping_district"
                                :placeholder="__('account.address.district_placeholder')" autocomplete="address-level2" required />
                        </div>

                        <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <p class="{{ $savedLine }} text-lh-muted" role="status">
                                @if (session('status') === 'address-updated')
                                    <x-lucide-circle-check class="size-4 shrink-0 stroke-[1.5] text-lh-heading" aria-hidden="true" />
                                    <span class="text-lh-heading">{{ __('account.address.saved') }}</span>
                                @elseif (blank($user->default_shipping_address))
                                    {{ __('account.address.empty') }}
                                @endif
                            </p>
                            <button type="submit" class="{{ $primaryButton }}" data-busy-label="{{ __('auth.busy') }}">
                                <span data-button-label>{{ __('account.address.submit') }}</span>
                            </button>
                        </div>
                    </form>
                </x-account.section>

                {{-- Những lần gửi thương --}}
                <x-account.section id="orders" :title="__('account.orders.title')"
                    :description="__('account.orders.summary', ['total' => count($orders), 'completed' => $completedCount, 'preparing' => $preparingCount])"
                    data-filter-group>
                    <div class="flex flex-wrap gap-2 px-5 pt-4 pb-6 sm:px-7" role="group" aria-label="{{ __('account.orders.filter_label') }}">
                        <button type="button" class="{{ $chip }}" data-filter-chip="all" aria-pressed="true">{{ __('account.orders.filter_all', ['count' => count($orders)]) }}</button>
                        <button type="button" class="{{ $chip }}" data-filter-chip="processing" aria-pressed="false">{{ __('account.orders.filter_processing', ['count' => $preparingCount]) }}</button>
                        <button type="button" class="{{ $chip }}" data-filter-chip="completed" aria-pressed="false">{{ __('account.orders.filter_completed', ['count' => $completedCount]) }}</button>
                    </div>

                    <ul aria-live="polite">
                        @foreach ($orders as $order)
                            <x-account.order-row :order="$order" />
                        @endforeach
                    </ul>
                    <p data-filter-empty hidden class="border-t border-lh-border px-5 py-8 text-center text-sm text-lh-muted sm:px-7">{{ __('account.orders.empty') }}</p>

                    @if ($latestPreparing)
                        <p class="bg-lh-sage-50 px-5 py-4 text-xs sm:px-7">{{ __('account.orders.latest_note', ['code' => $latestPreparing['code']]) }}</p>
                    @endif
                </x-account.section>

                {{-- Thay đổi mật khẩu --}}
                <x-account.section id="password" :title="__('account.password.title')"
                    :description="$hasPassword ? __('account.password.description') : __('account.password.google_description')">
                    <form method="POST" action="{{ route('account.password.update') }}" novalidate data-busy-form data-ready-form class="px-5 pt-6 pb-6 sm:px-7 sm:pb-7">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-5 md:grid-cols-2">
                            @if ($hasPassword)
                                <div class="md:col-span-2">
                                    <x-form.input name="current_password" type="password" :label="__('account.password.current')" marker
                                        autocomplete="current-password" required />
                                </div>
                            @endif
                            <x-form.input name="password" type="password" :label="__('account.password.new')" marker validate
                                :hint="__('account.password.hint')" autocomplete="new-password" required
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}" />
                            <x-form.input name="password_confirmation" type="password" :label="__('account.password.confirm')" marker validate
                                :hint="' '" autocomplete="new-password" required data-match="password"
                                data-hint-ok="{{ __('account.password.matched') }}" data-hint-bad="{{ __('account.password.not_matched') }}" />
                        </div>

                        <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-col gap-2" role="status">
                                @if (session('status') === 'password-updated')
                                    <p class="{{ $savedLine }} text-lh-heading">
                                        <x-lucide-circle-check class="size-4 shrink-0 stroke-[1.5]" aria-hidden="true" />
                                        {{ __('account.password.saved') }}
                                    </p>
                                @endif
                                <p class="text-xs text-lh-muted" data-ready-text
                                    data-ready="{{ __('account.password.ready') }}" data-not-ready="{{ __('account.password.not_ready') }}">{{ __('account.password.not_ready') }}</p>
                            </div>
                            <button type="submit" class="{{ $primaryButton }}" data-busy-label="{{ __('auth.busy') }}">
                                <span data-button-label>{{ __('account.password.submit') }}</span>
                            </button>
                        </div>
                    </form>
                </x-account.section>
            </div>
        </div>
    </div>
</x-layouts.storefront>
