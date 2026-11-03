@php
    /*
     * TODO: dữ liệu mẫu theo docs/designs/storefront/03-hoa-theo-mua.png — chỉ dựng giao diện.
     * Khi nối DB: mùa = categories (type = season), sản phẩm của mùa lấy theo bộ sưu tập; "giá từ" = giá size thấp nhất.
     * Mùa đang vào mùa và tháng trên tiêu đề hiện theo thiết kế (tháng 10).
     */
    $currentSeason = 'autumn';
    $seasonPrices = ['spring' => 620000, 'summer' => 550000, 'autumn' => 590000, 'winter' => 650000];
    $seasonProducts = [
        ['name' => 'Thu dịu dàng', 'slug' => 'thu-diu-dang', 'composition' => 'Hồng đào · Cúc mẫu đơn · Bình gốm', 'price' => 890000, 'badge' => __('seasons.products.badge')],
        ['name' => 'Một vệt nắng thu', 'slug' => 'mot-vet-nang-thu', 'composition' => 'Hồng cam · Cẩm chướng · Lá bạc', 'price' => 590000],
        ['name' => 'Hẹn với tháng Mười', 'slug' => 'hen-voi-thang-muoi', 'composition' => 'Cúc vàng · Hồng kem · Giỏ mây', 'price' => 720000],
        ['name' => 'Chiều êm', 'slug' => 'chieu-em', 'composition' => 'Thạch thảo · Hồng phấn · Bình gốm', 'price' => 650000],
    ];

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
    <section aria-labelledby="season-hero-title" class="grid bg-lh-blush lg:grid-cols-2">
        <x-flower-image name="hero-hoa-theo-mua" :alt="__('seasons.hero.image_alt')" class="aspect-[4/3] lg:aspect-auto lg:h-[520px]" />

        <div class="flex flex-col justify-center px-4 py-12 sm:px-8 lg:px-14 lg:py-16 xl:px-[72px]">
            <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ __('seasons.hero.eyebrow') }}</p>
            <h1 id="season-hero-title" class="mt-5 font-serif text-[38px] leading-[1.15] sm:text-5xl">
                {{ __('seasons.hero.title_line_1') }}<br>{{ __('seasons.hero.title_line_2') }}
            </h1>
            <p class="mt-6 max-w-[500px] text-[15px] leading-relaxed text-lh-muted">{{ __('seasons.hero.body') }}</p>
            <p class="mt-5 text-sm">{{ __('seasons.hero.collection', ['price' => $money($seasonPrices[$currentSeason])]) }}</p>
            <a href="#season-products-title" class="{{ $button }} mt-7 self-start bg-lh-primary text-white hover:bg-lh-primary-dark">
                {{ __('seasons.hero.cta') }}
                <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
            </a>
        </div>
    </section>

    {{-- 4 mùa --}}
    <section aria-labelledby="seasons-title" class="{{ $container }} pt-16 pb-14 lg:pt-20">
        <x-storefront.section-heading id="seasons-title" :eyebrow="__('seasons.seasons.eyebrow')" :title="__('seasons.seasons.title')">
            <x-slot:aside>{{ __('seasons.seasons.aside') }}</x-slot:aside>
        </x-storefront.section-heading>

        <ul class="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4" role="list">
            @foreach (__('seasons.seasons.items') as $season => $item)
                @php
                    $isCurrent = $season === $currentSeason;
                @endphp
                <li>
                    {{-- TODO: trỏ tới trang bộ sưu tập của từng mùa khi có. --}}
                    <a href="#" class="group block rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lh-primary"
                        @if ($isCurrent) aria-current="true" @endif>
                        <x-flower-image :name="'mua-'.$season" class="aspect-[4/3] rounded-sm transition-opacity group-hover:opacity-95" />
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
                        <span class="mt-3 block text-xs font-medium">{{ __('seasons.seasons.from', ['price' => $money($seasonPrices[$season])]) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Hoa của tháng --}}
    <section aria-labelledby="season-products-title" class="{{ $container }} pb-16 lg:pb-20">
        <div class="border-t border-lh-border pt-14">
            <x-storefront.section-heading id="season-products-title" class="scroll-mt-6" :eyebrow="__('seasons.products.eyebrow')"
                :title="__('seasons.products.title')" :description="__('seasons.products.description')">
                <x-slot:aside>{{ __('seasons.products.count', ['count' => count($seasonProducts)]) }}</x-slot:aside>
            </x-storefront.section-heading>

            <ul class="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4" role="list">
                @foreach ($seasonProducts as $product)
                    <li>
                        <x-storefront.product-card :name="$product['name']" :subtitle="$product['composition']" :price="$product['price']"
                            :image-name="$product['slug']" :badge="$product['badge'] ?? null" />
                    </li>
                @endforeach
            </ul>

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
