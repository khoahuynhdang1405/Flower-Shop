@props(['collection'])

@php
    $isPublished = $collection['status'] === 'published';
    $period = match (true) {
        filled($collection['period']) => $collection['period'],
        filled($collection['planned_at'] ?? null) => __('admin.collections.planned', [
            'date' => $collection['planned_at'],
        ]),
        default => __('admin.collections.all_year'),
    };
    $footerLink =
        'inline-flex items-center gap-2 rounded transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

{{-- Thẻ bộ sưu tập (Admin › Bộ sưu tập). data-* dùng cho tìm kiếm/lọc (resources/js/list-filter.js). --}}
<li data-list-item data-search="{{ $collection['name'] }}" data-type="{{ $collection['type'] }}"
    data-status="{{ $collection['status'] }}"
    class="flex flex-col overflow-hidden rounded-md border border-lh-border bg-lh-surface">
    <x-flower-image :name="$collection['image']" :alt="__('admin.collections.cover_alt', ['name' => $collection['name']])" class="aspect-[491/253] w-full" />

    <div class="flex flex-1 flex-col px-6 pt-6 pb-5">
        <div class="flex items-center justify-between gap-3">
            <p class="text-[13px] text-lh-muted">{{ __('admin.collections.types.' . $collection['type']) }}</p>
            <x-admin.badge
                :tone="$isPublished ? 'success' : 'neutral'">{{ __('admin.collections.statuses.' . $collection['status']) }}</x-admin.badge>
        </div>

        <h2 class="mt-4 font-serif text-[28px] leading-tight font-normal">{{ $collection['name'] }}</h2>
        <p class="mt-3 text-sm text-lh-muted">{{ $collection['tagline'] }}</p>
        <p class="mt-4 mb-5 text-[13px]">{{ __('admin.collections.products', ['count' => $collection['products']]) }} ·
            {{ $period }}</p>

        {{-- TODO: trang sửa / chi tiết bộ sưu tập. --}}
        <div class="mt-auto flex items-center justify-between gap-4 border-t border-lh-border pt-4 text-sm">
            <a href="#" class="{{ $footerLink }}"
                aria-label="{{ __('admin.list.edit_item', ['name' => $collection['name']]) }}">
                <x-lucide-pencil class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                {{ __('admin.list.edit') }}
            </a>
            <a href="#" class="{{ $footerLink }} text-[13px]"
                aria-label="{{ __('admin.list.view_item', ['name' => $collection['name']]) }}">
                {{ __('admin.list.view') }}
                <x-lucide-arrow-right class="size-3.5 stroke-[1.5]" aria-hidden="true" />
            </a>
        </div>
    </div>
</li>
