@php
    /**
     * @var \Illuminate\Support\Carbon $today
     * @var \App\Enums\Season $currentSeason  mùa hiện tại theo tháng (SeasonController)
     * @var array<string, string|null> $seasonCovers  giá trị Season => ảnh chính bộ sưu tập mùa (null: ảnh tĩnh)
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $seasonProducts  hoa trong bộ sưu tập của mùa hiện tại
     *
     * TODO: "giá từ" của 4 thẻ mùa vẫn là số cố định trong Season::startingPrice().
     */
    $season = $currentSeason->value;
    // vi dùng :month (Tháng 11), en dùng :month_name (November).
    $monthParams = ['month' => $today->month, 'month_name' => $today->copy()->locale('en')->monthName];

    $natureIcons = ['fresh' => 'brand.flower-mark', 'colors' => 'lucide-palette', 'gentle' => 'lucide-leaf'];
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $button = 'inline-flex h-12 items-center justify-center gap-3 rounded px-6 text-[15px] font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20';
@endphp

<x-layouts.storefront :title="__('seasons.title')">
    <div class="{{ $container }} py-5">
        <x-storefront.breadcrumb :items="[[__('storefront.menu.home'), url('/')], [__('seasons.title'), null]]" />
    </div>

    {{-- Hero mùa hiện tại --}}
    <x-storefront.season-banner :season="$currentSeason" :today="$today" id="season-hero-title" heading-tag="h1" :image-url="$seasonCovers[$season]"
        href="#season-products-title" :cta="__('seasons.hero.'.$season.'.cta')" />

    {{-- 4 mùa --}}
    <section aria-labelledby="seasons-title" class="{{ $container }} pt-16 pb-14 lg:pt-20">
        <x-storefront.section-heading id="seasons-title" :eyebrow="__('seasons.seasons.eyebrow')" :title="__('seasons.seasons.title')">
            <x-slot:aside>{{ __('seasons.seasons.aside') }}</x-slot:aside>
        </x-storefront.section-heading>

        <ul class="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4" role="list">
            @foreach (__('seasons.seasons.items') as $itemSeason => $item)
                @php
                    $isCurrent = $itemSeason === $season;
                @endphp
                <li>
                    {{-- TODO: trỏ tới trang bộ sưu tập của từng mùa khi có. --}}
                    <a href="#" class="group block rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lh-primary"
                        @if ($isCurrent) aria-current="true" @endif>
                        <x-cover-image :url="$seasonCovers[$itemSeason]" :name="'mua-'.$itemSeason" class="aspect-[4/3] rounded-sm transition-opacity group-hover:opacity-95" />
                        <p @class(['mt-4 text-[11px]', 'font-semibold text-lh-primary' => $isCurrent, 'text-lh-muted' => ! $isCurrent])>
                            {{ $item['months'] }}
                            @if ($isCurrent)
                                · {{ __('seasons.seasons.current') }}
                            @endif
                        </p>
                        <span class="mt-2 flex items-center justify-between gap-3">
                            <span class="font-serif text-xl">{{ $item['name'] }}</span>
                            <x-lucide-arrow-up-right class="size-4 shrink-0 stroke-[1.5] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 motion-reduce:transition-none" aria-hidden="true" />
                        </span>
                        <span class="mt-2 block text-xs text-lh-muted">{{ $item['body'] }}</span>
                        <span class="mt-3 block text-xs font-medium">{{ __('seasons.seasons.from', ['price' => $money(App\Enums\Season::from($itemSeason)->startingPrice())]) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Hoa của tháng --}}
    <section aria-labelledby="season-products-title" class="{{ $container }} pb-16 lg:pb-20">
        <div class="border-t border-lh-border pt-14">
            <x-storefront.section-heading id="season-products-title" class="scroll-mt-6" :eyebrow="__('seasons.products.eyebrow')"
                :title="__('seasons.products.title', $monthParams)" :description="__('seasons.products.descriptions.'.$season)">
                @if ($seasonProducts->isNotEmpty())
                    <x-slot:aside>{{ __('seasons.products.count', ['count' => $seasonProducts->count()]) }}</x-slot:aside>
                @endif
            </x-storefront.section-heading>

            @if ($seasonProducts->isEmpty())
                <p class="mt-8 rounded-sm bg-lh-sage-50 px-5 py-6 text-sm text-lh-muted">{{ __('seasons.products.empty') }}</p>
            @else
                <ul class="mt-8 grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-6 sm:gap-y-10 lg:grid-cols-4" role="list">
                    @foreach ($seasonProducts as $product)
                        <li>
                            <x-storefront.product-card :product="$product" :badge="$product->is_featured ? __('seasons.products.badge') : null" />
                        </li>
                    @endforeach
                </ul>
            @endif

            <p class="mt-8 text-xs text-lh-muted">{{ __('seasons.products.note') }}</p>
        </div>
    </section>

    {{-- Đúng mùa hoa, đúng lúc thương --}}
    <section aria-labelledby="nature-title" class="bg-lh-sage-50">
        <div class="{{ $container }} grid gap-10 py-14 lg:grid-cols-[minmax(0,1.1fr)_repeat(3,minmax(0,1fr))] lg:gap-12">
            <div>
                <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ __('seasons.nature.eyebrow') }}</p>
                <h2 id="nature-title" class="mt-3 font-serif text-3xl leading-tight">
                    {{ __('seasons.nature.title_line_1') }}<br>{{ __('seasons.nature.title_line_2') }}
                </h2>
            </div>
            @foreach (__('seasons.nature.items') as $key => $item)
                <div>
                    <x-dynamic-component :component="$natureIcons[$key]" aria-hidden="true"
                        :class="Arr::toCssClasses(['h-5 w-auto text-lh-primary', 'stroke-[1.5]' => str_starts_with($natureIcons[$key], 'lucide-')])" />
                    <h3 class="mt-3 text-sm font-medium">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-xs leading-relaxed text-lh-muted">{{ $item['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA tư vấn --}}
    <section aria-labelledby="season-cta-title" class="{{ $container }} py-16 text-center lg:py-20">
        <h2 id="season-cta-title" class="font-serif text-3xl leading-tight">{{ __('seasons.cta.title') }}</h2>
        <p class="mt-5 text-sm leading-relaxed text-lh-muted">{{ __('seasons.cta.body_line_1') }}<br>{{ __('seasons.cta.body_line_2') }}</p>
        {{-- TODO: trỏ tới kênh tư vấn (Zalo / Messenger / form) khi có. --}}
        <a href="#" class="{{ $button }} mt-8 border border-lh-primary text-lh-primary hover:bg-lh-sage-50">
            {{ __('seasons.cta.button') }}
            <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
        </a>
    </section>
</x-layouts.storefront>
