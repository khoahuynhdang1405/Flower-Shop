@php
    /*
     * Câu chuyện của Lá (docs/designs/storefront/09-cau-chuyen-cua-la.png) — trang tĩnh, nội dung trong lang/{locale}/story.php.
     * Ảnh: public/images/flowers/{tên}.webp|jpg; chưa có file thì hiện khung giữ chỗ màu sage.
     */
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20';
    $eyebrow = 'text-[11px] tracking-wide text-lh-heading uppercase';
    $philosophyIcons = ['selected' => 'brand.flower-mark', 'handmade' => 'lucide-hand-heart', 'card' => 'lucide-mail'];
@endphp

<x-layouts.storefront :title="__('story.title')">
    {{-- Hero --}}
    <section aria-labelledby="story-title" class="{{ $container }} grid items-center gap-12 py-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,560px)] lg:gap-20 lg:py-20">
        <div>
            <p class="{{ $eyebrow }}">{{ __('story.hero.eyebrow') }}</p>
            <h1 id="story-title" class="mt-6 font-serif text-[42px] leading-[1.15] sm:text-[56px]">
                @foreach (__('story.hero.title_lines') as $line)
                    {{ $line }}@unless ($loop->last)<br>@endunless
                @endforeach
            </h1>
            <p class="mt-7 max-w-[480px] text-[15px] leading-relaxed text-lh-muted">{{ __('story.hero.body') }}</p>
            <p class="mt-8 font-serif text-xl italic">{{ __('story.hero.quote') }}</p>
            <a href="#philosophy-title"
                class="mt-7 inline-flex items-center gap-2 rounded text-sm underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                {{ __('story.hero.more') }}
                <x-lucide-arrow-down class="size-4 stroke-[1.5]" aria-hidden="true" />
            </a>
        </div>

        <figure class="mx-auto w-full max-w-[560px]">
            <x-auth.arch-image name="cau-chuyen-hero" :alt="__('story.hero.image_alt')" aspect="aspect-[420/345]" />
            <figcaption class="mt-3 ml-1 font-serif text-sm text-lh-muted italic">{{ __('story.hero.caption') }}</figcaption>
        </figure>
    </section>

    {{-- Ba điều Lá & Hoa luôn nâng niu --}}
    <section aria-labelledby="philosophy-title" class="scroll-mt-24 bg-lh-sage-50">
        <div class="{{ $container }} py-16 lg:py-20">
            <div class="text-center">
                <p class="{{ $eyebrow }}">{{ __('story.philosophy.eyebrow') }}</p>
                <h2 id="philosophy-title" class="mt-3 font-serif text-3xl leading-tight sm:text-[40px]">{{ __('story.philosophy.title') }}</h2>
                <p class="mt-4 text-sm text-lh-muted">{{ __('story.philosophy.description') }}</p>
            </div>

            <ul class="mt-12 grid gap-10 md:grid-cols-3 md:gap-8" role="list">
                @foreach (__('story.philosophy.items') as $key => $item)
                    <li>
                        <x-flower-image :name="'triet-ly-'.$key" :alt="$item['image_alt']" class="aspect-[567/340] rounded-sm" />
                        <h3 class="mt-6 flex items-center gap-3 font-serif text-2xl">
                            <x-dynamic-component :component="$philosophyIcons[$key]" class="size-5 shrink-0 stroke-[1.5] text-lh-heading" aria-hidden="true" />
                            {{ $item['title'] }}
                        </h3>
                        <p class="mt-4 text-sm leading-relaxed text-lh-muted">{{ $item['body'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Một ngày bình dị tại Lá & Hoa --}}
    <section aria-labelledby="day-title" class="{{ $container }} py-16 lg:py-24">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,360px)] lg:items-end lg:gap-16">
            <div>
                <p class="{{ $eyebrow }}">{{ __('story.day.eyebrow') }}</p>
                <h2 id="day-title" class="mt-3 font-serif text-3xl leading-tight sm:text-[40px]">{{ __('story.day.title') }}</h2>
            </div>
            <p class="text-sm leading-relaxed text-lh-muted">{{ __('story.day.aside') }}</p>
        </div>

        <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,800fr)_minmax(0,890fr)] lg:gap-[88px]">
            <figure>
                <div class="flex flex-col gap-6">
                    <x-flower-image name="mot-ngay-cat-hoa" :alt="__('story.day.image_alt')" class="aspect-[800/514] rounded-sm" />
                    <x-flower-image name="mot-ngay-goi-hoa" :alt="__('story.day.detail_alt')" class="aspect-[800/347] rounded-sm" />
                </div>
                <figcaption class="mt-4 font-serif text-sm text-lh-muted italic">{{ __('story.day.caption') }}</figcaption>
            </figure>

            <ol class="flex flex-col" role="list">
                @foreach (__('story.day.moments') as $moment)
                    <li class="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-x-6 border-b border-lh-border py-7 first:pt-0 sm:grid-cols-[5.5rem_minmax(0,1fr)]">
                        <time class="font-serif text-2xl leading-tight text-lh-primary tabular-nums sm:text-[28px]">{{ $moment['time'] }}</time>
                        <div>
                            <h3 class="font-serif text-xl leading-snug sm:text-2xl">{{ $moment['title'] }}</h3>
                            <p class="mt-3 text-sm leading-relaxed text-lh-muted">{{ $moment['body'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Bạn kể chuyện. Lá chọn hoa. --}}
    <section aria-labelledby="story-cta-title" class="bg-lh-blush">
        <div class="{{ $container }} grid gap-8 py-14 lg:grid-cols-[minmax(0,1fr)_minmax(0,330px)] lg:items-center lg:gap-16 lg:py-16">
            <div>
                <p class="{{ $eyebrow }}">{{ __('story.cta.eyebrow') }}</p>
                <h2 id="story-cta-title" class="mt-3 font-serif text-3xl leading-tight sm:text-[40px]">{{ __('story.cta.title') }}</h2>
                <p class="mt-4 max-w-[560px] text-[15px] leading-relaxed text-lh-muted">{{ __('story.cta.body') }}</p>
            </div>
            <div class="flex flex-col gap-3">
                <x-storefront.consult-button>{{ __('story.cta.button') }}</x-storefront.consult-button>
                <p class="text-xs text-lh-muted">{{ __('story.cta.note') }}</p>
            </div>
        </div>
    </section>
</x-layouts.storefront>
