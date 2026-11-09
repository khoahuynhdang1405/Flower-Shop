@php
    /**
     * @var \App\DTOs\Cart\CartSummaryData $summary
     *
     * Bảng màu riêng của trang (Figma): 2F5142 → lh-addon-accent, 737C72 → lh-addon-muted, EDF1E8 → lh-addon-selected;
     * 263F34 / DCE1D6 / FAF8F3 lệch 1 đơn vị so với lh-primary-dark / lh-border / lh-cream nên dùng token sẵn có.
     */
    $lines = $summary->lines;
    $money = fn (int $amount): string => number_format($amount, 0, '', __('storefront.thousands_separator')).' '.__('storefront.currency');
    $container = 'mx-auto max-w-[1440px] px-4 sm:px-8 lg:px-20 xl:px-[120px]';
    $textLink = 'inline-flex items-center gap-3 rounded text-[15px] text-lh-primary-dark underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-lh-primary';
@endphp

<x-layouts.storefront :title="__('cart.page.title')">
    {{-- resources/js/cart.js: các form [data-cart-form] gửi bằng fetch rồi thay [data-cart], không tải lại trang. --}}
    <p class="sr-only" aria-live="polite" data-cart-announcer></p>

    <div class="{{ $container }} pt-5 pb-16 lg:pb-24" data-cart>
        <x-storefront.breadcrumb :items="[
            [__('storefront.menu.home'), url('/')],
            [__('cart.page.breadcrumb'), null],
        ]" />

        <div class="mt-8 flex flex-col gap-6 lg:mt-12 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 tabindex="-1" data-cart-focus-fallback class="font-serif text-[40px] leading-tight text-lh-primary-dark focus:outline-none sm:text-[46px]">{{ __('cart.page.title') }}</h1>
                <p class="mt-3 text-[15px] text-lh-addon-muted sm:text-base">{{ __('cart.page.intro') }}</p>
            </div>
            <x-storefront.checkout-stepper :current="1" class="lg:mb-12" />
        </div>

        @if (session('status'))
            <p class="mt-6 flex items-center gap-2 text-sm text-lh-primary-dark" role="status">
                <x-lucide-check class="size-4 stroke-2" aria-hidden="true" />
                {{ session('status') }}
            </p>
        @endif
        @if ($errors->hasAny(['quantity', 'item']))
            <p class="mt-6 text-sm text-lh-danger" role="alert">{{ $errors->first('quantity') ?: $errors->first('item') }}</p>
        @endif

        @if ($lines->isEmpty())
            <section class="mt-10 flex flex-col items-center rounded-md bg-lh-addon-selected px-6 py-16 text-center lg:mt-12">
                <x-brand.flower-mark class="h-14 w-auto text-lh-addon-accent" />
                <h2 class="mt-6 font-serif text-[28px] leading-tight text-lh-primary-dark">{{ __('cart.empty.title') }}</h2>
                <p class="mt-3 max-w-[46ch] text-[15px] leading-relaxed text-lh-addon-muted">{{ __('cart.empty.body') }}</p>
                <a href="{{ route('flowers.index') }}"
                    class="mt-8 inline-flex h-12 items-center gap-3 rounded-sm bg-lh-addon-accent px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    {{ __('cart.empty.cta') }}
                    <x-lucide-arrow-right class="size-4 stroke-[1.5]" aria-hidden="true" />
                </a>
            </section>
        @else
            <div class="mt-10 grid items-start gap-10 lg:mt-12 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-12 xl:grid-cols-[minmax(0,1fr)_445px] xl:gap-14">
                <section aria-labelledby="cart-items-title">
                    <h2 id="cart-items-title" class="sr-only">{{ __('cart.table.caption') }}</h2>

                    <div class="grid grid-cols-[minmax(0,1fr)_auto_8rem] gap-x-6 border-b border-lh-border pb-4 text-xs tracking-wide text-lh-addon-muted uppercase max-sm:hidden" aria-hidden="true">
                        <span>{{ __('cart.table.product') }}</span>
                        <span class="w-[110px] text-center">{{ __('cart.table.quantity') }}</span>
                        <span class="text-right">{{ __('cart.table.total') }}</span>
                    </div>

                    <ul class="max-sm:border-t max-sm:border-lh-border">
                        @foreach ($lines->flowers as $flower)
                            @php($sizeParams = ['product' => $flower->productId, 'size' => $flower->sizeId])
                            <x-storefront.cart-line
                                :name="$flower->name"
                                serif
                                :details="array_values(array_filter([
                                    $flower->sizeName ? __('cart.line.size', ['size' => Str::lower($flower->sizeName)]) : null,
                                    __('cart.line.unit_price', ['price' => $money($flower->unitPrice)]),
                                ]))"
                                :quantity="$flower->quantity"
                                :total="$flower->total()"
                                :update-action="route('cart.flowers.update', $sizeParams)"
                                :remove-action="route('cart.flowers.destroy', $sizeParams)"
                                :edit-url="route('flowers.show', $flower->slug)"
                                :image-url="$flower->imageUrl"
                                :image-name="$flower->slug" />
                        @endforeach

                        @foreach ($lines->paidAddons() as $addon)
                            <x-storefront.cart-line
                                :name="$addon->name"
                                :details="[__('cart.line.unit_price', ['price' => $money($addon->unitPrice)])]"
                                :quantity="$addon->quantity"
                                :total="$addon->total()"
                                :update-action="route('cart.addons.update', $addon->addonId)"
                                :remove-action="route('cart.addons.destroy', $addon->addonId)"
                                :image-url="$addon->imageUrl" />
                        @endforeach
                    </ul>

                    @foreach ($lines->freeAddons() as $gift)
                        @php($giftName = Str::lower($gift->name))
                        <div class="mt-8 flex items-start gap-4 rounded-md bg-lh-addon-selected px-5 py-5 sm:px-7 sm:py-6">
                            <x-lucide-mail class="mt-0.5 size-6 shrink-0 stroke-[1.5] text-lh-primary-dark" aria-hidden="true" />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                                    <h3 class="text-base font-medium text-lh-primary-dark">{{ __('cart.free_gift.title', ['name' => $giftName]) }}</h3>
                                    <p class="text-sm text-lh-primary-dark">{{ __('cart.free_gift.price') }}</p>
                                </div>
                                <p class="mt-1.5 max-w-[52ch] text-sm leading-relaxed text-lh-addon-muted">{{ __('cart.free_gift.body', ['name' => $giftName]) }}</p>
                                <form method="POST" action="{{ route('cart.addons.destroy', $gift->addonId) }}" class="mt-2" data-cart-form>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="{{ __('cart.free_gift.remove_label', ['name' => $gift->name]) }}"
                                        class="cursor-pointer rounded text-[13px] text-lh-addon-muted underline-offset-4 hover:text-lh-primary-dark hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                                        {{ __('cart.line.remove') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    <a href="{{ route('flowers.index') }}" class="{{ $textLink }} mt-8">
                        <x-lucide-arrow-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                        {{ __('cart.continue') }}
                    </a>
                </section>

                <div class="lg:sticky lg:top-8">
                    <x-storefront.cart-summary :summary="$summary" />
                    <p class="mt-8 flex items-start gap-3 px-2 text-sm leading-relaxed text-lh-addon-muted sm:px-4">
                        <x-lucide-leaf class="mt-0.5 size-5 shrink-0 stroke-[1.5] text-lh-primary-dark" aria-hidden="true" />
                        {{ __('cart.summary.fresh_note') }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-layouts.storefront>
