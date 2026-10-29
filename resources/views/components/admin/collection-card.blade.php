@props(['category'])

@php
    $status = $category->is_active ? 'published' : 'hidden';
    $footerLink = 'inline-flex items-center gap-2 rounded transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

{{-- Thẻ bộ sưu tập (Admin › Bộ sưu tập). data-* dùng cho tìm kiếm/lọc (resources/js/list-filter.js). --}}
<li data-list-item data-search="{{ $category->name }} {{ $category->slug }}" data-type="{{ $category->type->value }}"
    data-status="{{ $status }}"
    class="flex flex-col overflow-hidden rounded-md border border-lh-border bg-lh-surface">
    {{-- Ảnh bìa: public/images/flowers/{slug}.webp|jpg (chưa có cột ảnh bìa — docs/design-vs-db-gaps.md #2). --}}
    <x-flower-image :name="$category->slug" :alt="__('admin.collections.cover_alt', ['name' => $category->name])" class="aspect-[491/253] w-full" />

    <div class="flex flex-1 flex-col px-6 pt-6 pb-5">
        <div class="flex items-center justify-between gap-3">
            <p class="text-[13px] text-lh-muted">{{ $category->type->label() }}</p>
            <x-admin.badge :tone="$category->is_active ? 'success' : 'neutral'">{{ __('admin.collections.statuses.'.$status) }}</x-admin.badge>
        </div>

        <h2 class="mt-4 font-serif text-[28px] leading-tight font-normal [overflow-wrap:anywhere]">{{ $category->name }}</h2>
        @if (filled($category->description))
            <p class="mt-3 line-clamp-2 text-sm text-lh-muted">{{ $category->description }}</p>
        @endif
        <p class="mt-4 mb-5 text-[13px]">{{ __('admin.collections.products', ['count' => (int) $category->products_count]) }}</p>

        <div class="mt-auto flex items-center justify-between gap-4 border-t border-lh-border pt-4 text-sm">
            <a href="{{ route('admin.collections.edit', $category) }}" class="{{ $footerLink }}"
                aria-label="{{ __('admin.list.edit_item', ['name' => $category->name]) }}">
                <x-lucide-pencil class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                {{ __('admin.list.edit') }}
            </a>
            <x-admin.delete-button :action="route('admin.collections.destroy', $category)"
                :label="__('admin.list.delete_item', ['name' => $category->name])"
                :confirm="__('admin.collections.confirm_delete', ['name' => $category->name])" />
        </div>
    </div>
</li>
