@php
    /**
     * @var Illuminate\Database\Eloquent\Collection<int, App\Models\Product> $featuredProducts
     * @var Illuminate\Support\Collection<int, App\Models\Category> $featuredCategories
     *
     * Thành phần hoa ("Hồng vàng · Cúc tana · Lá bạc") và nhãn "Được yêu thích / Thiết kế mới" chưa có cột
     * (docs/design-vs-db-gaps.md #5): dòng phụ tạm hiện tên danh mục, chưa có nhãn.
     */
    $promiseIcons = ['fresh' => 'lucide-sprout', 'crafted' => 'lucide-hand-heart', 'on_time' => 'lucide-truck', 'card' => 'lucide-mail-open'];
    $chip = 'h-9 rounded-full border border-lh-border px-4 text-[13px] transition-colors hover:border-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary aria-pressed:border-lh-primary aria-pressed:bg-lh-primary aria-pressed:text-white';
    $button = 'inline-flex h-12 items-center justify-center gap-3 rounded px-6 text-[15px] font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20';
@endphp

<x-layouts.storefront :title="__('home.title')">
    {{-- Hero --}}
    <section aria-labelledby="hero-title" class="{{ $container }} grid items-center gap-12 py-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,500px)] lg:gap-20 lg:py-20">
        <div>
            <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ __('home.hero.eyebrow') }}</p>
            <h1 id="hero-title" class="mt-5 font-serif text-[42px] leading-[1.15] sm:text-[56px]">
                {{ __('home.hero.title_line_1') }}<br>{{ __('home.hero.title_line_2') }}
            </h1>
            <p class="mt-6 max-w-[440px] text-[15px] leading-relaxed text-lh-muted">{{ __('home.hero.body') }}</p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ Route::has('flowers.index') ? route('flowers.index') : '#' }}" class="{{ $button }} bg-lh-primary text-white hover:bg-lh-primary-dark">
                    {{ __('home.hero.primary_cta') }}
                    <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                </a>
                <a href="{{ Route::has('story') ? route('story') : '#' }}" class="{{ $button }} border border-lh-primary bg-lh-surface text-lh-primary hover:bg-lh-sage-50">
                    {{ __('home.hero.secondary_cta') }}
                    <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                </a>
            </div>

            <p class="mt-8 flex items-center gap-2 text-xs text-lh-muted">
                <x-lucide-sprout class="size-4 stroke-[1.5] text-lh-primary" aria-hidden="true" />
                {{ __('home.hero.note') }}
            </p>
        </div>

        <div class="relative mx-auto w-full max-w-[500px]">
            <x-auth.arch-image name="hero-trang-chu" :alt="__('home.hero.image_alt')" />

            <a href="{{ route('seasons') }}"
                class="absolute right-4 bottom-4 flex items-center gap-6 rounded-sm bg-lh-surface px-5 py-4 shadow-sm transition-colors hover:bg-lh-cream focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary sm:right-6 sm:bottom-6">
                <span>
                    <span class="block text-[10px] tracking-wide text-lh-muted uppercase">{{ __('home.hero.collection_eyebrow') }}</span>
                    <span class="mt-1 block font-serif text-lg">{{ __('home.hero.collection_name') }}</span>
                </span>
                <x-lucide-arrow-up-right class="size-4 stroke-[1.5]" aria-hidden="true" />
            </a>
        </div>
    </section>

    {{-- 4 cam kết --}}
    <section aria-label="{{ __('home.promises.label') }}" class="bg-lh-sage-50">
        <ul class="{{ $container }} grid gap-6 py-8 sm:grid-cols-2 lg:grid-cols-4" role="list">
            @foreach (__('home.promises.items') as $key => $promise)
                <li class="flex items-start gap-3">
                    <x-dynamic-component :component="$promiseIcons[$key]" class="mt-0.5 size-5 shrink-0 stroke-[1.5] text-lh-primary" aria-hidden="true" />
                    <div>
                        <p class="text-sm font-medium">{{ $promise['title'] }}</p>
                        <p class="mt-1 text-xs text-lh-muted">{{ $promise['body'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Những bó hoa được thương nhất --}}
    <section aria-labelledby="featured-title" data-filter-group class="{{ $container }} py-16 lg:py-24">
        <x-storefront.section-heading id="featured-title" :eyebrow="__('home.featured.eyebrow')" :title="__('home.featured.title')">
            <x-slot:aside>
                <a href="{{ Route::has('flowers.index') ? route('flowers.index') : '#' }}"
                    class="inline-flex items-center gap-2 rounded text-sm text-lh-heading underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    {{ __('home.featured.view_all') }}
                    <x-lucide-arrow-up-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                </a>
            </x-slot:aside>
        </x-storefront.section-heading>

        @if ($featuredProducts->isEmpty())
            <p class="mt-8 rounded-sm border border-dashed border-lh-border px-6 py-10 text-center text-sm text-lh-muted">
                {{ __('home.featured.none') }}
            </p>
        @else
            @if ($featuredCategories->count() > 1)
                <div role="group" aria-label="{{ __('home.featured.filter_label') }}" class="mt-8 flex flex-wrap gap-2">
                    <button type="button" class="{{ $chip }}" data-filter-chip="all" aria-pressed="true">{{ __('home.featured.all') }}</button>
                    @foreach ($featuredCategories as $category)
                        <button type="button" class="{{ $chip }}" data-filter-chip="{{ $category->slug }}" aria-pressed="false">{{ $category->name }}</button>
                    @endforeach
                </div>
            @endif

            <ul class="mt-8 grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4" role="list" aria-live="polite">
                @foreach ($featuredProducts as $product)
                    <li data-filter-item data-filter-value="{{ $product->category->slug }}">
                        <x-storefront.product-card :name="$product->name" :subtitle="$product->category->name" :price="$product->display_price"
                            :image-url="$product->primaryImage?->url" :image-name="$product->slug" />
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.storefront>
