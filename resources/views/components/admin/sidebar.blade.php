@props(['orderCount' => 0])

@php
    $badges = ['orders' => $orderCount];
    $linkFor = fn (array $item): string => isset($item['url'])
        ? url($item['url'])
        : (Route::has($item['route']) ? route($item['route']) : '#');
    $isActive = fn (array $item): bool => isset($item['route'])
        && Route::has($item['route'])
        && request()->routeIs($item['route'], $item['route'].'.*');
@endphp

{{-- Lớp phủ khi mở drawer trên màn hình nhỏ --}}
<div data-admin-drawer-overlay hidden class="fixed inset-0 z-30 bg-lh-primary-dark/50 lg:hidden"></div>

<aside id="admin-sidebar" data-admin-drawer
    class="fixed inset-y-0 left-0 z-40 flex w-56 flex-col overflow-y-auto bg-lh-primary-dark px-5 pt-7 pb-10 text-white
        transition-[translate,visibility] duration-200 ease-out max-lg:data-open:transition-[translate] motion-reduce:transition-none
        max-lg:invisible max-lg:-translate-x-full max-lg:data-open:visible max-lg:data-open:translate-x-0">

    <button type="button" data-admin-drawer-close
        class="absolute top-3 right-3 grid size-9 place-items-center rounded text-white/80 hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-white lg:hidden">
        <x-lucide-x class="size-5 stroke-[1.5]" aria-hidden="true" />
        <span class="sr-only">{{ __('admin.nav.close') }}</span>
    </button>

    <div class="flex flex-col items-center">
        <x-logo :href="route('admin.dashboard')" />
        <p class="mt-4 text-[11px] tracking-wide text-white/85 uppercase">{{ __('admin.workspace') }}</p>
    </div>

    <nav aria-label="{{ __('admin.nav.main') }}" class="mt-7">
        <ul class="flex flex-col gap-2">
            @foreach (config('admin.menu') as $item)
                @php
                    $active = $isActive($item);
                    $badge = isset($item['badge']) ? ($badges[$item['badge']] ?? 0) : 0;
                @endphp
                <li>
                    <a href="{{ $linkFor($item) }}" @if ($active) aria-current="page" @endif
                        @class([
                            'flex items-center gap-3 rounded px-3.5 py-3 text-[15px] leading-[22px] transition-colors',
                            'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white',
                            'bg-lh-primary text-white' => $active,
                            'text-white/90 hover:bg-white/5 hover:text-white' => ! $active,
                        ])>
                        <span class="grid size-5 shrink-0 place-items-center">
                            <x-dynamic-component :component="$item['icon']" aria-hidden="true"
                                :class="Arr::toCssClasses(['h-5 w-auto max-w-5', 'stroke-[1.5]' => str_starts_with($item['icon'], 'lucide-')])" />
                        </span>
                        <span>{{ __('admin.menu.'.$item['key']) }}</span>
                        @if ($badge > 0)
                            <span aria-hidden="true" class="text-xs text-white/80">{{ $badge }}</span>
                            <span class="sr-only">({{ __('admin.nav.orders_badge', ['count' => $badge]) }})</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <hr class="my-7 border-lh-on-dark-divider">

    <nav aria-label="{{ __('admin.nav.secondary') }}">
        <ul class="flex flex-col">
            @foreach (config('admin.secondary_menu') as $item)
                <li>
                    <a href="{{ $linkFor($item) }}"
                        class="block rounded py-2.5 text-[13px] text-white/85 transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                        {{ __('admin.menu.'.$item['key']) }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <figure class="mt-auto pt-16">
        <x-lucide-sprout class="size-6 text-white/90 stroke-[1.5]" aria-hidden="true" />
        <blockquote class="mt-5 font-serif text-lg leading-normal text-white/90 italic">
            <p>{{ __('admin.quote.line_1') }}<br>{{ __('admin.quote.line_2') }}</p>
        </blockquote>
    </figure>
</aside>
