@props([
    'productName',
    /**
     * @var list<array{url?: string, image?: string, video_duration?: string}> $items
     *   url = ảnh đã upload (storage); không có url thì image = tên file trong public/images/flowers
     */
    'items',
])

{{--
    Gallery chi tiết sản phẩm: ảnh lớn + hàng ảnh nhỏ. Ảnh đầu hiện sẵn (chạy được khi tắt JS);
    resources/js/product-gallery.js đổi ảnh lớn khi bấm ảnh nhỏ hoặc dùng phím mũi tên.
    TODO: ô video chỉ là ảnh tĩnh — product_images chưa có loại media video (docs/design-vs-db-gaps.md #6).
--}}
<div {{ $attributes }} data-gallery>
    <div class="relative">
        <div class="relative aspect-square overflow-hidden rounded-md bg-lh-sage-100">
            @foreach ($items as $item)
                <div data-gallery-slide @if ($loop->first) data-active @else aria-hidden="true" @endif
                    class="absolute inset-0 opacity-0 transition-opacity duration-300 data-active:opacity-100 motion-reduce:transition-none">
                    @isset($item['url'])
                        <img src="{{ $item['url'] }}" alt="{{ __('product.gallery.image_alt', ['position' => $loop->iteration, 'name' => $productName]) }}"
                            class="size-full object-cover" @unless ($loop->first) loading="lazy" @endunless>
                    @else
                        <x-flower-image :name="$item['image']" class="size-full"
                            :alt="__('product.gallery.image_alt', ['position' => $loop->iteration, 'name' => $productName])" />
                    @endisset
                    @isset($item['video_duration'])
                        <div class="absolute inset-0 grid place-items-center bg-lh-primary-dark/20">
                            <span class="flex items-center gap-2 rounded-full bg-lh-surface/95 px-4 py-2 text-xs text-lh-heading">
                                <x-lucide-play class="size-4 stroke-[1.5]" aria-hidden="true" />
                                {{ __('product.gallery.video_soon') }}
                            </span>
                        </div>
                    @endisset
                </div>
            @endforeach
        </div>

        {{-- TODO: danh sách yêu thích (docs/design-vs-db-gaps.md #12). --}}
        <button type="button" aria-label="{{ __('storefront.product.favorite', ['name' => $productName]) }}"
            class="absolute top-4 right-4 grid size-10 place-items-center rounded-full bg-lh-surface text-lh-heading transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
            <x-lucide-heart class="size-5 stroke-[1.5]" aria-hidden="true" />
        </button>
    </div>

    <div role="group" aria-label="{{ __('product.gallery.label', ['name' => $productName]) }}" class="mt-3 grid grid-cols-4 gap-2 sm:gap-3">
        @foreach ($items as $item)
            <button type="button" data-gallery-thumb aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                aria-label="{{ isset($item['video_duration']) ? __('product.gallery.video', ['duration' => $item['video_duration']]) : __('product.gallery.thumb', ['position' => $loop->iteration]) }}"
                class="relative block overflow-hidden rounded-sm opacity-80 outline-offset-2 transition-opacity hover:opacity-100 focus-visible:outline-2 focus-visible:outline-lh-primary aria-pressed:opacity-100 aria-pressed:ring-2 aria-pressed:ring-lh-primary motion-reduce:transition-none">
                @isset($item['url'])
                    <img src="{{ $item['url'] }}" alt="" loading="lazy" class="aspect-[11/9] w-full bg-lh-sage-100 object-cover">
                @else
                    <x-flower-image :name="$item['image']" class="aspect-[11/9]" />
                @endisset
                @isset($item['video_duration'])
                    <span class="absolute inset-0 grid place-items-center" aria-hidden="true">
                        <span class="grid size-8 place-items-center rounded-full border border-white/80 text-white">
                            <x-lucide-play class="size-4 stroke-[1.5]" />
                        </span>
                    </span>
                    <span class="absolute right-1.5 bottom-1.5 rounded-sm bg-lh-primary-dark/80 px-1.5 py-0.5 text-[10px] text-white max-sm:hidden" aria-hidden="true">
                        {{ __('product.gallery.video', ['duration' => $item['video_duration']]) }}
                    </span>
                @endisset
            </button>
        @endforeach
    </div>

    <p class="mt-4 max-w-[400px] text-[11px] leading-relaxed text-lh-muted">{{ __('product.gallery.note') }}</p>
</div>
