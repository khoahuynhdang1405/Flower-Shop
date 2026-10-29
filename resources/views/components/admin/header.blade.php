@props(['fallbackPage' => null])

@php
    $admin = auth('admin')->user();
    $adminName = $admin?->name ?? '';
    $adminRole = $admin?->role?->slug ?? 'staff';

    $currentItem = collect(config('admin.menu'))
        ->first(fn (array $item): bool => request()->routeIs($item['route'], $item['route'].'.*'));
    $pageLabel = $currentItem ? __('admin.menu.'.$currentItem['key']) : $fallbackPage;

    $nameParts = preg_split('/\s+/u', trim($adminName));
    $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 1).(count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : ''));
@endphp

<header class="sticky top-0 z-20 flex h-19 items-center gap-3 border-b border-lh-border bg-lh-surface px-4 sm:px-6 lg:px-8">
    <button type="button" data-admin-drawer-toggle aria-controls="admin-sidebar" aria-expanded="false"
        class="-ml-2 grid size-10 place-items-center rounded text-lh-heading hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary lg:hidden">
        <x-lucide-menu class="size-5 stroke-[1.5]" aria-hidden="true" />
        <span class="sr-only">{{ __('admin.nav.open') }}</span>
    </button>

    <nav aria-label="{{ __('admin.header.breadcrumb') }}" class="min-w-0">
        <ol class="flex items-center gap-2 text-sm whitespace-nowrap text-lh-muted">
            <li><a href="{{ route('admin.dashboard') }}" class="rounded hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-lh-primary">{{ __('admin.header.root') }}</a></li>
            @if ($pageLabel)
                <li aria-hidden="true">/</li>
                <li class="truncate"><span aria-current="page">{{ $pageLabel }}</span></li>
            @endif
        </ol>
    </nav>

    <div class="ml-auto flex items-center gap-2 sm:gap-5">
        <x-locale-switcher />

        {{-- TODO: danh sách thông báo. --}}
        <button type="button"
            class="grid size-10 place-items-center rounded-full text-lh-heading hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary">
            <x-lucide-bell class="size-5 stroke-[1.5]" aria-hidden="true" />
            <span class="sr-only">{{ __('admin.header.notifications') }}</span>
        </button>

        <span aria-hidden="true" class="hidden h-6 w-px bg-lh-border sm:block"></span>

        {{-- TODO: trang hồ sơ quản trị. --}}
        <details class="relative" data-dropdown>
            <summary
                class="flex cursor-pointer list-none items-center gap-3 rounded-full py-1 pr-1 pl-1 text-left hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-lh-primary sm:gap-5 sm:rounded sm:pr-2 [&::-webkit-details-marker]:hidden">
                <span aria-hidden="true" class="grid size-9 shrink-0 place-items-center rounded-full bg-lh-sage-50 text-xs font-medium text-lh-heading">{{ $initials }}</span>
                <span class="hidden flex-col sm:flex">
                    <span class="text-sm leading-5 font-semibold">{{ $adminName }}</span>
                    <span class="text-xs leading-4 text-lh-muted">{{ __('admin.roles.'.$adminRole) }}</span>
                </span>
                <span class="sr-only sm:hidden">{{ __('admin.header.account', ['name' => $adminName]) }}</span>
                <x-lucide-chevron-down class="hidden size-4 text-lh-heading sm:block stroke-[1.5]" aria-hidden="true" />
            </summary>
            <div class="absolute top-full right-0 z-30 mt-3 w-52 rounded-md border border-lh-border bg-lh-surface p-2 shadow-sm">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-2 rounded px-3 py-2.5 text-left text-sm hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-lh-primary">
                        <x-lucide-log-out class="size-4 stroke-[1.5]" aria-hidden="true" />
                        {{ __('admin.header.logout') }}
                    </button>
                </form>
            </div>
        </details>
    </div>
</header>
