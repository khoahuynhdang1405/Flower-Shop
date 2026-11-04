@php
    /**
     * Cột "Lọc hoa" (docs/designs/storefront/02-tat-ca-hoa.png). Form GET giữ lựa chọn trên query string;
     * resources/js/catalog-filters.js gửi form bằng fetch rồi chỉ thay lưới hoa, không tải lại trang.
     *
     * @var Illuminate\Database\Eloquent\Collection<int, App\Models\Category> $flowerTypes  kèm products_count
     * @var Illuminate\Database\Eloquent\Collection<int, App\Models\Category> $occasions
     *
     * TODO: chưa lọc theo "chỉ hoa còn hàng" — nhóm này chỉ là giao diện.
     */
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
                <a href="{{ route('flowers.index') }}" data-catalog-reset
                    class="rounded text-[11px] text-lh-muted underline underline-offset-4 hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    {{ __('flowers.filters.clear') }}
                </a>
            </div>

            <form id="flower-filters" method="GET" action="{{ route('flowers.index') }}" data-catalog-form data-catalog-region="filters">
                {{-- Kiểu hoa --}}
                @if ($flowerTypes->isNotEmpty())
                <details open class="{{ $group }} group/filter">
                    <summary class="{{ $summary }}">
                        {{ __('flowers.filters.type') }}
                        <x-lucide-chevron-down class="size-4 stroke-[1.5] transition-transform group-open/filter:rotate-180 motion-reduce:transition-none" aria-hidden="true" />
                    </summary>
                    <fieldset class="mt-4 grid gap-3">
                        <legend class="sr-only">{{ __('flowers.filters.type') }}</legend>
                        @foreach ($flowerTypes as $flowerType)
                            <label class="{{ $option }}">
                                <input type="checkbox" name="type[]" value="{{ $flowerType->slug }}" class="{{ $checkbox }}" @checked(in_array($flowerType->slug, $selectedTypes, true))>
                                {{ $flowerType->name }} ({{ $flowerType->products_count }})
                            </label>
                        @endforeach
                    </fieldset>
                </details>
                @endif

                {{-- Dịp gửi thương --}}
                @if ($occasions->isNotEmpty())
                <details open class="{{ $group }} group/filter">
                    <summary class="{{ $summary }}">
                        {{ __('flowers.filters.occasion') }}
                        <x-lucide-chevron-down class="size-4 stroke-[1.5] transition-transform group-open/filter:rotate-180 motion-reduce:transition-none" aria-hidden="true" />
                    </summary>
                    <fieldset class="mt-4 grid gap-3">
                        <legend class="sr-only">{{ __('flowers.filters.occasion') }}</legend>
                        @foreach ($occasions as $occasion)
                            <label class="{{ $option }}">
                                <input type="checkbox" name="occasion[]" value="{{ $occasion->slug }}" class="{{ $checkbox }}" @checked(in_array($occasion->slug, $selectedOccasions, true))>
                                {{ $occasion->name }}
                            </label>
                        @endforeach
                    </fieldset>
                </details>
                @endif

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
                        @foreach (App\Enums\PriceRange::cases() as $priceRange)
                            <label class="{{ $option }}">
                                <input type="radio" name="price" value="{{ $priceRange->value }}" class="{{ $radio }}" @checked($selectedPrice === $priceRange->value)>
                                {{ __('flowers.filters.prices.'.$priceRange->value) }}
                            </label>
                        @endforeach
                    </fieldset>
                </details>

                {{-- Sắc hoa: checkbox ẩn, hiển thị thành chấm màu. --}}
                <fieldset class="{{ $group }}">
                    <legend class="float-left w-full text-sm font-medium">{{ __('flowers.filters.color') }}</legend>
                    <div class="clear-both flex flex-wrap gap-2.5 pt-4">
                        @foreach (App\Enums\FlowerColor::cases() as $color)
                            <x-flower-color-swatch :color="$color" name="color[]" :checked="in_array($color->value, $selectedColors, true)" class="ring-offset-lh-cream" />
                        @endforeach
                    </div>
                    <p class="mt-3 text-[11px] text-lh-muted" aria-hidden="true">
                        {{ collect(App\Enums\FlowerColor::cases())->map(fn (App\Enums\FlowerColor $color): string => $color->label())->implode(' · ') }}
                    </p>
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
