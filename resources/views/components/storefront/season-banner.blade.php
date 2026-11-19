@props([
    'season',
    'today',
    'id',
    // h1 ở trang Hoa theo mùa, h2 ở Trang chủ.
    'headingTag' => 'h2',
    'href',
    'cta',
])

@php
    /**
     * @var App\Enums\Season $season  mùa hiện tại (Season::fromMonth)
     * @var Carbon\CarbonInterface $today
     *
     * Banner mùa nền hồng phấn (lh-blush): ảnh trái, lời mời phải. Dùng ở Trang chủ và đầu trang Hoa theo mùa.
     */
    $key = $season->value;
    // vi dùng :month (Tháng 11), en dùng :month_name (November).
    $monthParams = ['month' => $today->month, 'month_name' => $today->copy()->locale('en')->monthName];
    $money = number_format($season->startingPrice(), 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
@endphp

<section aria-labelledby="{{ $id }}" {{ $attributes->class('grid bg-lh-blush lg:grid-cols-2') }}>
    <x-flower-image name="hero-hoa-theo-mua" :alt="__('seasons.hero.image_alt')" class="aspect-[4/3] lg:aspect-auto lg:h-[520px]" />

    <div class="flex flex-col justify-center px-4 py-12 sm:px-8 lg:px-14 lg:py-16 xl:px-[72px]">
        <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ __('seasons.hero.eyebrow', $monthParams) }}</p>
        <{{ $headingTag }} id="{{ $id }}" class="mt-5 font-serif text-[38px] leading-[1.15] sm:text-5xl">
            {{ __("seasons.hero.{$key}.title_line_1") }}<br>{{ __("seasons.hero.{$key}.title_line_2") }}
        </{{ $headingTag }}>
        <p class="mt-6 max-w-[500px] text-[15px] leading-relaxed text-lh-muted">{{ __("seasons.hero.{$key}.body") }}</p>
        <p class="mt-5 text-sm">{{ __("seasons.hero.{$key}.collection", ['price' => $money]) }}</p>
        <a href="{{ $href }}"
            class="mt-7 inline-flex h-12 items-center justify-center gap-3 self-start rounded bg-lh-primary px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
            {{ $cta }}
            <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
        </a>
    </div>
</section>
