@props([
    // Header gọn cho trang đăng nhập/đăng ký: ẩn tìm kiếm và giỏ hàng.
    'compact' => false,
    // Số hoa + quà tính tiền trong giỏ — AppServiceProvider gắn qua view composer.
    'cartCount' => 0,
])

@php
    $linkFor = fn (array $item): string => isset($item['url'])
        ? url($item['url'])
        : (Route::has($item['route']) ? route($item['route']) : '#');
    $isActive = fn (array $item): bool => isset($item['url'])
        ? request()->is(ltrim($item['url'], '/') ?: '/')
        : Route::has($item['route']) && request()->routeIs($item['route'], $item['route'].'.*');
    $iconButton = 'grid size-10 place-items-center rounded-full text-lh-heading transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp
<header class="relative border-b border-lh-border">
    <div class="mx-auto grid h-20 max-w-[1440px] grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-x-3 px-4 sm:gap-x-6 sm:px-8 lg:h-[98px] lg:grid-cols-[1fr_auto_1fr] lg:px-20">
        <x-logo />

        <nav aria-label="{{ __('storefront.nav.main') }}" class="min-w-0 max-lg:hidden">
            <ul class="mx-auto flex w-max items-center gap-5 text-sm sm:gap-7 lg:gap-8 xl:gap-11">
                @foreach (config('shop.menu') as $item)
                    <li>
                        <a href="{{ $linkFor($item) }}" @if ($isActive($item)) aria-current="page" @endif
                            class="block whitespace-nowrap rounded py-2 underline-offset-8 transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary aria-[current=page]:underline">
                            {{ __('storefront.menu.'.$item['key']) }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="flex items-center justify-end gap-0.5 sm:gap-2">
            {{-- Dưới lg: menu chính gập vào nút ☰, mở thành danh sách dọc ngay dưới header (details chạy được không cần JS). --}}
            <details class="group lg:hidden" data-dropdown>
                <summary class="{{ $iconButton }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                    <x-lucide-menu class="size-5 stroke-[1.5] group-open:hidden" aria-hidden="true" />
                    <x-lucide-x class="hidden size-5 stroke-[1.5] group-open:block" aria-hidden="true" />
                    <span class="sr-only">{{ __('storefront.nav.menu') }}</span>
                </summary>
                <nav aria-label="{{ __('storefront.nav.main') }}" class="absolute inset-x-0 top-full z-30 border-b border-lh-border bg-lh-cream px-4 pb-4 shadow-sm sm:px-8">
                    <ul class="flex flex-col">
                        @foreach (config('shop.menu') as $item)
                            <li class="border-t border-lh-border first:border-t-0">
                                <a href="{{ $linkFor($item) }}" @if ($isActive($item)) aria-current="page" @endif
                                    class="flex min-h-12 items-center rounded text-[15px] transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary aria-[current=page]:font-semibold aria-[current=page]:text-lh-primary">
                                    {{ __('storefront.menu.'.$item['key']) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </details>

            @unless ($compact)
                {{-- TODO: trang tìm kiếm. --}}
                <a href="#" class="{{ $iconButton }} max-sm:hidden">
                    <x-lucide-search class="size-5 stroke-[1.5]" aria-hidden="true" />
                    <span class="sr-only">{{ __('storefront.nav.search') }}</span>
                </a>
            @endunless

            @guest
                <a href="{{ route('login') }}" class="{{ $iconButton }}">
                    <x-lucide-user class="size-5 stroke-[1.5]" aria-hidden="true" />
                    <span class="sr-only">{{ __('storefront.nav.login') }}</span>
                </a>
            @else
                <details class="relative" data-dropdown>
                    <summary class="{{ $iconButton }} cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                        <x-lucide-user class="size-5 stroke-[1.5]" aria-hidden="true" />
                        <span class="sr-only">{{ __('storefront.nav.account') }}</span>
                    </summary>
                    <div class="absolute top-full right-0 z-30 mt-3 w-56 rounded-md border border-lh-border bg-lh-surface p-2 shadow-sm">
                        <p class="truncate px-3 py-2 text-sm font-medium">{{ auth()->user()->name }}</p>
                        <a href="{{ route('account') }}"
                            class="flex items-center gap-2 rounded px-3 py-2.5 text-sm hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary">
                            <x-lucide-user class="size-4 stroke-[1.5]" aria-hidden="true" />
                            {{ __('storefront.nav.my_account') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 rounded px-3 py-2.5 text-left text-sm hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary">
                                <x-lucide-log-out class="size-4 stroke-[1.5]" aria-hidden="true" />
                                {{ __('storefront.nav.logout') }}
                            </button>
                        </form>
                    </div>
                </details>
            @endguest

            <x-storefront.locale-menu :trigger-class="$iconButton" />

            @unless ($compact)
                <a href="{{ route('cart.show') }}" @if (request()->routeIs('cart.show')) aria-current="page" @endif data-cart-count
                    class="flex h-10 items-center gap-1.5 rounded-full px-2 text-sm text-lh-heading transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary">
                    <x-lucide-shopping-bag class="size-5 stroke-[1.5]" aria-hidden="true" />
                    <span aria-hidden="true" data-cart-count-number>({{ $cartCount }})</span>
                    <span class="sr-only" data-cart-count-label="{{ __('storefront.nav.cart', ['count' => ':count']) }}">{{ __('storefront.nav.cart', ['count' => $cartCount]) }}</span>
                </a>
            @endunless
        </div>
    </div>
</header>
