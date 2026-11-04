@php
    /**
     * Trang "Tất cả hoa" (docs/designs/storefront/02-tat-ca-hoa.png).
     *
     * @var Illuminate\Pagination\LengthAwarePaginator<int, App\Models\Product> $products
     *
     * Các vùng data-catalog-region được resources/js/catalog-filters.js thay khi lọc / chuyển trang (không tải lại trang).
     * Thành phần hoa và nhãn chưa có cột (docs/design-vs-db-gaps.md #5): dòng phụ tạm hiện tên danh mục, chưa có nhãn.
     */
    $currentPage = $products->currentPage();
    $lastPage = $products->lastPage();
    // Bốn trang đầu, trang cuối và hai trang kề trang hiện tại; khoảng trống giữa chúng hiện "…".
    $pages = collect(range(1, $lastPage))->filter(fn (int $page): bool => $page <= 4 || $page === $lastPage || abs($page - $currentPage) <= 1)->values();

    // TODO: chưa sắp xếp theo lựa chọn này (chưa có dữ liệu "được yêu thích"); danh sách đang xếp mới nhất trước.
    $sort = (string) request()->query('sort', 'popular');
    $pageButton = 'grid size-8 place-items-center rounded-sm text-xs tabular-nums transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $button = 'inline-flex h-12 items-center justify-center gap-3 rounded px-6 text-[15px] font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20';
@endphp

<x-layouts.storefront :title="__('flowers.title')">
    {{-- Tiêu đề trang --}}
    <section aria-labelledby="flowers-title" class="{{ $container }} pt-5 pb-10 lg:pb-12">
        <x-storefront.breadcrumb :items="[[__('storefront.menu.home'), url('/')], [__('flowers.title'), null]]" />

        <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div>
                <p class="text-[11px] font-semibold tracking-wide text-lh-primary uppercase">{{ __('flowers.hero.eyebrow') }}</p>
                <h1 id="flowers-title" class="mt-4 font-serif text-[34px] leading-tight sm:text-[44px]">{{ __('flowers.hero.title') }}</h1>
                <p class="mt-4 max-w-[600px] text-sm leading-relaxed text-lh-muted">{{ __('flowers.hero.body') }}</p>
            </div>

            <div class="lg:mr-6 lg:pb-1">
                <x-lucide-sprout class="size-5 stroke-[1.5] text-lh-primary" aria-hidden="true" />
                <p class="mt-2 font-serif text-lg italic">{{ __('flowers.hero.promise') }}</p>
                <p class="mt-1 text-[11px] text-lh-muted">{{ __('flowers.hero.promise_note') }}</p>
            </div>
        </div>
    </section>

    <div class="{{ $container }} grid gap-8 pb-16 lg:grid-cols-[236px_minmax(0,1fr)] lg:gap-10 lg:pb-20">
        @include('flowers.partials.filters')

        <section aria-label="{{ __('flowers.results.label') }}" data-catalog-results
            class="scroll-mt-6 transition-opacity aria-busy:opacity-60 motion-reduce:transition-none">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-[13px] text-lh-muted" aria-live="polite" data-catalog-region="count">
                    @if ($products->total() > 0)
                        {{ __('flowers.results.count', ['from' => $products->firstItem(), 'to' => $products->lastItem(), 'total' => $products->total()]) }}
                    @endif
                </p>

                {{-- Thuộc form bộ lọc (form="flower-filters") để giữ lựa chọn khi đổi cách sắp xếp. --}}
                <label class="select h-11 w-auto rounded-sm border-lh-border bg-lh-surface text-[13px] text-lh-heading" data-catalog-region="sort">
                    <span class="label text-lh-heading">{{ __('flowers.results.sort') }}:</span>
                    <select name="sort" form="flower-filters">
                        @foreach (__('flowers.results.sorts') as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div data-catalog-region="results">
                @if ($products->isEmpty())
                    <div class="mt-6 rounded-sm border border-dashed border-lh-border px-6 py-14 text-center">
                        <p class="text-sm text-lh-muted">{{ __('flowers.results.empty') }}</p>
                        <a href="{{ route('flowers.index') }}" data-catalog-reset
                            class="mt-4 inline-flex items-center gap-1.5 rounded text-sm font-medium text-lh-primary underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                            {{ __('flowers.results.empty_reset') }}
                            <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                        </a>
                    </div>
                @else
                    <ul class="mt-6 grid gap-x-6 gap-y-10 sm:grid-cols-2 xl:grid-cols-3" role="list">
                        @foreach ($products as $product)
                            <li>
                                <x-storefront.product-card :name="$product->name" :subtitle="$product->category->name" :price="$product->display_price"
                                    :image-url="$product->primaryImage?->url" :image-name="$product->slug" :url="route('flowers.show', $product->slug)" />
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($lastPage > 1)
                    <nav aria-label="{{ __('flowers.pagination.label') }}" class="mt-12">
                        <ul class="flex items-center justify-center gap-2" role="list">
                            <li>
                                @if ($currentPage > 1)
                                    <a href="{{ $products->previousPageUrl() }}" data-catalog-page class="{{ $pageButton }} hover:bg-lh-sage-50" rel="prev">
                                        <x-lucide-chevron-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                                        <span class="sr-only">{{ __('flowers.pagination.previous') }}</span>
                                    </a>
                                @else
                                    <span class="{{ $pageButton }} text-lh-border" aria-hidden="true"><x-lucide-chevron-left class="size-4 stroke-[1.5]" /></span>
                                @endif
                            </li>

                            @foreach ($pages as $page)
                                @if ($loop->index > 0 && $page - $pages[$loop->index - 1] > 1)
                                    <li aria-hidden="true"><span class="{{ $pageButton }} border border-lh-border bg-lh-surface text-lh-muted">…</span></li>
                                @endif
                                <li>
                                    <a href="{{ $products->url($page) }}" data-catalog-page aria-label="{{ __('flowers.pagination.page', ['page' => $page]) }}"
                                        @if ($page === $currentPage) aria-current="page" @endif
                                        class="{{ $pageButton }} border border-lh-border bg-lh-surface hover:border-lh-primary aria-[current=page]:border-lh-primary aria-[current=page]:bg-lh-primary aria-[current=page]:text-white">
                                        {{ $page }}
                                    </a>
                                </li>
                            @endforeach

                            <li>
                                @if ($currentPage < $lastPage)
                                    <a href="{{ $products->nextPageUrl() }}" data-catalog-page class="{{ $pageButton }} hover:bg-lh-sage-50" rel="next">
                                        <x-lucide-chevron-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                                        <span class="sr-only">{{ __('flowers.pagination.next') }}</span>
                                    </a>
                                @else
                                    <span class="{{ $pageButton }} text-lh-border" aria-hidden="true"><x-lucide-chevron-right class="size-4 stroke-[1.5]" /></span>
                                @endif
                            </li>
                        </ul>
                    </nav>
                @endif
            </div>
        </section>
    </div>

    {{-- CTA thiết kế riêng --}}
    <section aria-labelledby="flowers-cta-title" class="bg-lh-sage-50">
        <div class="{{ $container }} flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 id="flowers-cta-title" class="font-serif text-3xl leading-tight">{{ __('flowers.cta.title') }}</h2>
                <p class="mt-3 text-sm text-lh-muted">{{ __('flowers.cta.body') }}</p>
            </div>
            {{-- TODO: trỏ tới kênh tư vấn (Zalo / Messenger / form) khi có. --}}
            <a href="#" class="{{ $button }} shrink-0 self-start bg-lh-primary text-white hover:bg-lh-primary-dark sm:self-auto">
                {{ __('flowers.cta.button') }}
                <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
            </a>
        </div>
    </section>
</x-layouts.storefront>
