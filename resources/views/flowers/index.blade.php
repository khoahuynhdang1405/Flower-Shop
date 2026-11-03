@php
    /*
     * TODO: dữ liệu mẫu theo docs/designs/storefront/02-tat-ca-hoa.png — chỉ dựng giao diện, bộ lọc / sắp xếp / phân trang chưa lọc dữ liệu.
     * Khi nối DB: 9 sản phẩm/trang, giá = giá size mặc định. Thành phần hoa và nhãn chưa có cột (docs/design-vs-db-gaps.md #5).
     * Giá "Lời thì thầm" lấy 690.000 theo màn này; còn lệch với trang chi tiết (docs/design-vs-db-gaps.md, mục mâu thuẫn #2).
     */
    $products = [
        ['name' => 'Nắng trong veo', 'slug' => 'nang-trong-veo', 'composition' => 'Hồng vàng · Cúc tana · Lá bạc', 'price' => 550000, 'badge' => 'popular'],
        ['name' => 'Lời thì thầm', 'slug' => 'loi-thi-tham', 'composition' => 'Hồng pastel · Cẩm chướng · Baby', 'price' => 690000],
        ['name' => 'Một ngày bình yên', 'slug' => 'mot-ngay-binh-yen', 'composition' => 'Hồng kem · Cúc trắng · Lá xanh', 'price' => 750000, 'badge' => 'new'],
        ['name' => 'Một chút mộng mơ', 'slug' => 'mot-chut-mong-mo', 'composition' => 'Tulip hồng · Cúc tana · Lá bạc', 'price' => 620000],
        ['name' => 'Thu dịu dàng', 'slug' => 'thu-diu-dang', 'composition' => 'Hồng đào · Cúc mẫu đơn · Hoa mùa thu', 'price' => 890000, 'badge' => 'month'],
        ['name' => 'Gửi một niềm vui', 'slug' => 'gui-mot-niem-vui', 'composition' => 'Cúc tana · Hồng trắng · Bình gốm', 'price' => 480000],
        ['name' => 'Hồng trong tim', 'slug' => 'hong-trong-tim', 'composition' => 'Hồng đỏ · Cẩm chướng · Lá bạc', 'price' => 790000],
        ['name' => 'Mây trắng', 'slug' => 'may-trang', 'composition' => 'Cẩm tú cầu · Hồng kem · Giỏ mây', 'price' => 950000],
        ['name' => 'Nàng thơ', 'slug' => 'nang-tho', 'composition' => 'Hồng phấn · Mao lương · Hộp hoa', 'price' => 820000],
    ];
    $total = 48;
    $perPage = 9;
    $lastPage = (int) ceil($total / $perPage);
    $currentPage = min(max(request()->integer('page', 1), 1), $lastPage);
    // Trang hiện tại, hai trang kề và hai đầu; khoảng trống giữa chúng hiện "…".
    $pages = collect(range(1, $lastPage))->filter(fn (int $page): bool => $page <= 4 || $page === $lastPage || abs($page - $currentPage) <= 1)->values();

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

        <section aria-label="{{ __('flowers.results.label') }}">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="text-[13px] text-lh-muted">
                    {{ __('flowers.results.count', ['from' => ($currentPage - 1) * $perPage + 1, 'to' => min($currentPage * $perPage, $total), 'total' => $total]) }}
                </p>

                {{-- Thuộc form bộ lọc (form="flower-filters") để giữ lựa chọn khi đổi cách sắp xếp. --}}
                <label class="select h-11 w-auto rounded-sm border-lh-border bg-lh-surface text-[13px] text-lh-heading">
                    <span class="label text-lh-heading">{{ __('flowers.results.sort') }}:</span>
                    <select name="sort" form="flower-filters">
                        @foreach (__('flowers.results.sorts') as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <ul class="mt-6 grid gap-x-6 gap-y-10 sm:grid-cols-2 xl:grid-cols-3" role="list">
                @foreach ($products as $product)
                    <li>
                        <x-storefront.product-card :name="$product['name']" :subtitle="$product['composition']" :price="$product['price']"
                            :image-name="$product['slug']" :badge="isset($product['badge']) ? __('flowers.results.badges.'.$product['badge']) : null" />
                    </li>
                @endforeach
            </ul>

            <nav aria-label="{{ __('flowers.pagination.label') }}" class="mt-12">
                <ul class="flex items-center justify-center gap-2" role="list">
                    <li>
                        @if ($currentPage > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="{{ $pageButton }} hover:bg-lh-sage-50" rel="prev">
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
                            <a href="{{ request()->fullUrlWithQuery(['page' => $page]) }}" aria-label="{{ __('flowers.pagination.page', ['page' => $page]) }}"
                                @if ($page === $currentPage) aria-current="page" @endif
                                class="{{ $pageButton }} border border-lh-border bg-lh-surface hover:border-lh-primary aria-[current=page]:border-lh-primary aria-[current=page]:bg-lh-primary aria-[current=page]:text-white">
                                {{ $page }}
                            </a>
                        </li>
                    @endforeach

                    <li>
                        @if ($currentPage < $lastPage)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="{{ $pageButton }} hover:bg-lh-sage-50" rel="next">
                                <x-lucide-chevron-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                                <span class="sr-only">{{ __('flowers.pagination.next') }}</span>
                            </a>
                        @else
                            <span class="{{ $pageButton }} text-lh-border" aria-hidden="true"><x-lucide-chevron-right class="size-4 stroke-[1.5]" /></span>
                        @endif
                    </li>
                </ul>
            </nav>
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
