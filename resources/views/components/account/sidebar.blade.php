@props(['customer', 'active' => 'profile'])

@php
    $items = [
        'profile' => ['icon' => 'lucide-user', 'href' => '#profile'],
        'orders' => ['icon' => 'lucide-shopping-bag', 'href' => '#orders'],
        'password' => ['icon' => 'lucide-lock', 'href' => '#password'],
    ];
    $itemClass = 'flex w-full items-center gap-3 rounded px-3.5 py-3 text-left text-sm transition-colors focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

{{-- Thẻ bên trái trang Tài khoản: avatar, nhóm khách, menu các mục, đăng xuất, lời nhắn hỗ trợ. --}}
<aside class="rounded-md bg-lh-sage-50 p-5 lg:sticky lg:top-6">
    <div class="flex flex-col items-center text-center">
        <span aria-hidden="true" class="grid size-14 place-items-center rounded-full bg-lh-primary font-serif text-xl text-white">{{ $customer['initials'] }}</span>
        <p class="mt-3 text-[15px] font-semibold">{{ $customer['name'] }}</p>
        <p class="mt-2 text-xs">{{ $customer['group'] }}</p>
        <p class="mt-2 text-xs text-lh-muted">{{ __('account.member_since', ['date' => $customer['member_since']]) }}</p>
    </div>

    <hr class="my-5 border-lh-border">

    <nav aria-label="{{ __('account.nav.label') }}">
        <ul class="flex flex-col gap-1">
            @foreach ($items as $key => $item)
                <li>
                    <a href="{{ $item['href'] }}" @if ($key === $active) aria-current="true" @endif
                        @class([$itemClass, 'bg-lh-surface font-medium' => $key === $active, 'hover:bg-lh-surface/60' => $key !== $active])>
                        <x-dynamic-component :component="$item['icon']" class="size-[18px] shrink-0 stroke-[1.5]" aria-hidden="true" />
                        {{ __('account.nav.'.$key) }}
                    </a>
                </li>
            @endforeach
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="{{ $itemClass }} hover:bg-lh-surface/60">
                        <x-lucide-log-out class="size-[18px] shrink-0 stroke-[1.5]" aria-hidden="true" />
                        {{ __('account.nav.logout') }}
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <p class="mt-5 text-xs leading-relaxed text-lh-muted">{{ __('account.help', ['hotline' => config('shop.hotline')]) }}</p>
</aside>
