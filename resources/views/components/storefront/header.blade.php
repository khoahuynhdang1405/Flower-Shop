@php
    $linkFor = fn (array $item): string => isset($item['url'])
        ? url($item['url'])
        : (Route::has($item['route']) ? route($item['route']) : '#');
    $isActive = fn (array $item): bool => isset($item['url'])
        ? request()->is(ltrim($item['url'], '/') ?: '/')
        : Route::has($item['route']) && request()->routeIs($item['route'], $item['route'].'.*');
    $iconButton = 'grid size-10 place-items-center rounded-full text-lh-heading transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp
<header class="border-b border-lh-border">
    <div class="mx-auto grid h-20 max-w-[1440px] grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-x-3 px-4 sm:gap-x-6 sm:px-8 lg:h-[98px] lg:grid-cols-[1fr_auto_1fr] lg:px-20">
        <x-logo />

        <nav aria-label="{{ __('storefront.nav.main') }}" class="min-w-0 overflow-x-auto [scrollbar-width:none]">
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

        <div class="flex items-center justify-end gap-1 sm:gap-3">
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
                        {{-- TODO: link trang Tài khoản của tôi khi có. --}}
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
        </div>
    </div>
</header>
