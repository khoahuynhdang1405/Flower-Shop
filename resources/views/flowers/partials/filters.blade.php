@php
    /*
     * Cột "Lọc hoa" (docs/designs/storefront/02-tat-ca-hoa.png). Form GET giữ lựa chọn trên query string,
     * để khi có backend chỉ cần đọc lại các tham số type[], occasion[], price, color[], in_stock.
     * TODO: chưa có cột kiểu hoa / sắc hoa (docs/design-vs-db-gaps.md #3) — số lượng và mã màu là dữ liệu mẫu.
     */
    $typeCounts = ['bouquet' => 24, 'basket' => 14, 'vase' => 10];
    $swatches = ['pink' => '#dfa3ae', 'peach' => '#f2d9cf', 'yellow' => '#e2b85a', 'white' => '#ffffff', 'red' => '#a5434a', 'green' => '#b5c9ae'];

    $hasQuery = request()->query() !== [];
    $selectedTypes = (array) request()->query('type', []);
    $selectedOccasions = (array) request()->query('occasion', []);
    $selectedColors = (array) request()->query('color', []);
    $selectedPrice = (string) request()->query('price', '');
    // Thiết kế mặc định bật "chỉ hoa còn hàng"; khi khách đã lọc thì theo lựa chọn của khách.
    $onlyInStock = $hasQuery ? request()->boolean('in_stock') : true;

    $group = 'border-b border-lh-border py-5';
    $summary = 'flex cursor-pointer list-none items-center justify-between rounded text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary [&::-webkit-details-marker]:hidden';
    $option = 'flex cursor-pointer items-center gap-3 text-[13px] text-lh-heading';
    $checkbox = 'checkbox checkbox-primary checkbox-sm rounded-[3px] border-lh-border bg-lh-surface checked:border-lh-primary checked:bg-lh-primary';
    $radio = 'radio radio-primary radio-sm border-lh-border bg-lh-surface checked:border-lh-primary';
@endphp

<aside aria-labelledby="filters-title">
    {{-- Mở sẵn để chạy được khi tắt JS; trên màn hình nhỏ resources/js/catalog-filters.js thu gọn lại. --}}
    <details open data-collapse-below-lg class="group/panel">
        <summary class="{{ $summary }} h-12 rounded border border-lh-border bg-lh-surface px-4 lg:hidden">
            <span class="flex items-center gap-2">
                <x-lucide-sliders-horizontal class="size-4 stroke-[1.5]" aria-hidden="true" />
                {{ __('flowers.filters.toggle') }}
            </span>
            <x-lucide-chevron-down class="size-4 stroke-[1.5] transition-transform group-open/panel:rotate-180 motion-reduce:transition-none" aria-hidden="true" />
        </summary>

        <div class="max-lg:mt-4">
            <div class="flex items-baseline justify-between gap-4 pb-1">
                <h2 id="filters-title" class="font-serif text-2xl">{{ __('flowers.filters.title') }}</h2>
                <a href="{{ route('flowers.index') }}"
                    class="rounded text-[11px] text-lh-muted underline underline-offset-4 hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    {{ __('flowers.filters.clear') }}
                </a>
            </div>

            <form id="flower-filters" method="GET" action="{{ route('flowers.index') }}" data-auto-submit>
                {{-- Kiểu hoa --}}
                <details open class="{{ $group }} group/filter">
                    <summary class="{{ $summary }}">
                        {{ __('flowers.filters.type') }}
                        <x-lucide-chevron-down class="size-4 stroke-[1.5] transition-transform group-open/filter:rotate-180 motion-reduce:transition-none" aria-hidden="true" />
                    </summary>
                    <fieldset class="mt-4 grid gap-3">
                        <legend class="sr-only">{{ __('flowers.filters.type') }}</legend>
                        @foreach (__('flowers.filters.types') as $value => $label)
                            <label class="{{ $option }}">
                                <input type="checkbox" name="type[]" value="{{ $value }}" class="{{ $checkbox }}" @checked(in_array($value, $selectedTypes, true))>
                                {{ $label }} ({{ $typeCounts[$value] }})
                            </label>
                        @endforeach
                    </fieldset>
                </details>

                {{-- Dịp gửi thương --}}
                <details open class="{{ $group }} group/filter">
                    <summary class="{{ $summary }}">
                        {{ __('flowers.filters.occasion') }}
                        <x-lucide-chevron-down class="size-4 stroke-[1.5] transition-transform group-open/filter:rotate-180 motion-reduce:transition-none" aria-hidden="true" />
                    </summary>
                    <fieldset class="mt-4 grid gap-3">
                        <legend class="sr-only">{{ __('flowers.filters.occasion') }}</legend>
                        @foreach (__('flowers.filters.occasions') as $value => $label)
                            <label class="{{ $option }}">
                                <input type="checkbox" name="occasion[]" value="{{ $value }}" class="{{ $checkbox }}" @checked(in_array($value, $selectedOccasions, true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </fieldset>
                </details>

                {{-- Khoảng giá: chỉ chọn một khoảng nên dùng radio. --}}
                <details open class="{{ $group }} group/filter">
                    <summary class="{{ $summary }}">
                        {{ __('flowers.filters.price') }}
                        <x-lucide-chevron-down class="size-4 stroke-[1.5] transition-transform group-open/filter:rotate-180 motion-reduce:transition-none" aria-hidden="true" />
                    </summary>
                    <fieldset class="mt-4 grid gap-3">
                        <legend class="sr-only">{{ __('flowers.filters.price') }}</legend>
                        <label class="{{ $option }}">
                            <input type="radio" name="price" value="" class="{{ $radio }}" @checked($selectedPrice === '')>
                            {{ __('flowers.filters.price_any') }}
                        </label>
                        @foreach (__('flowers.filters.prices') as $value => $label)
                            <label class="{{ $option }}">
                                <input type="radio" name="price" value="{{ $value }}" class="{{ $radio }}" @checked($selectedPrice === (string) $value)>
                                {{ $label }}
                            </label>
                        @endforeach
                    </fieldset>
                </details>

                {{-- Sắc hoa: checkbox ẩn, hiển thị thành chấm màu. --}}
                <fieldset class="{{ $group }}">
                    <legend class="float-left w-full text-sm font-medium">{{ __('flowers.filters.color') }}</legend>
                    <div class="clear-both flex flex-wrap gap-2.5 pt-4">
                        @foreach (__('flowers.filters.colors') as $value => $label)
                            <label title="{{ $label }}"
                                class="relative size-[22px] cursor-pointer rounded-full border border-lh-border ring-lh-primary ring-offset-2 ring-offset-lh-cream transition-shadow has-checked:ring-2 has-focus-visible:outline-2 has-focus-visible:outline-offset-4 has-focus-visible:outline-lh-primary motion-reduce:transition-none"
                                style="background-color: {{ $swatches[$value] }}">
                                <input type="checkbox" name="color[]" value="{{ $value }}" class="sr-only" @checked(in_array($value, $selectedColors, true))>
                                <span class="sr-only">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-3 text-[11px] text-lh-muted" aria-hidden="true">{{ implode(' · ', __('flowers.filters.colors')) }}</p>
                </fieldset>

                <div class="py-5">
                    <label class="{{ $option }}">
                        <input type="checkbox" name="in_stock" value="1" class="{{ $checkbox }}" @checked($onlyInStock)>
                        {{ __('flowers.filters.in_stock') }}
                    </label>
                </div>

                {{-- Có JS thì form tự gửi khi đổi lựa chọn. --}}
                <noscript>
                    <button type="submit" class="h-10 w-full rounded bg-lh-primary text-sm font-medium text-white hover:bg-lh-primary-dark">
                        {{ __('flowers.filters.apply') }}
                    </button>
                </noscript>
            </form>

            <div class="mt-2 rounded-sm bg-lh-sage-50 p-5">
                <p class="font-serif text-xl leading-snug">{{ __('flowers.advice.title') }}</p>
                <p class="mt-3 text-xs leading-relaxed text-lh-muted">{{ __('flowers.advice.body') }}</p>
                {{-- TODO: trỏ tới kênh tư vấn (Zalo / Messenger / form) khi có. --}}
                <a href="#"
                    class="mt-4 inline-flex items-center gap-1.5 rounded text-xs font-medium text-lh-primary hover:underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    {{ __('flowers.advice.link') }}
                    <x-lucide-arrow-right class="size-3.5 stroke-[1.5]" aria-hidden="true" />
                </a>
            </div>
        </div>
    </details>
</aside>
