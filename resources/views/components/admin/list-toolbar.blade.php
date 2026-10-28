@props([
    'placeholder',
    // [['key' => 'type', 'label' => 'Lọc theo loại', 'all' => 'Tất cả loại', 'options' => ['season' => 'Theo mùa', ...]], ...]
    'filters' => [],
])

{{-- Ô tìm kiếm + các ô lọc cho danh sách admin; lọc phía trình duyệt bằng resources/js/list-filter.js. --}}
<div {{ $attributes->class('flex flex-col gap-3 md:flex-row') }}>
    <label class="relative min-w-0 flex-1">
        <span class="sr-only">{{ __('admin.list.search') }}</span>
        <x-lucide-search class="pointer-events-none absolute inset-y-0 left-4 my-auto size-5 stroke-[1.5] text-lh-heading" aria-hidden="true" />
        <input type="search" data-list-search placeholder="{{ $placeholder }}" autocomplete="off"
            class="h-12 w-full rounded border border-lh-border bg-lh-surface pr-4 pl-12 text-[15px] text-lh-heading placeholder:text-lh-muted focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary">
    </label>

    @foreach ($filters as $filter)
        <label class="relative md:w-48">
            <span class="sr-only">{{ $filter['label'] }}</span>
            <select data-list-select="{{ $filter['key'] }}"
                class="h-12 w-full appearance-none rounded border border-lh-border bg-lh-surface pr-11 pl-4 text-[15px] text-lh-heading focus:outline-2 focus:-outline-offset-1 focus:outline-lh-primary">
                <option value="">{{ $filter['all'] }}</option>
                @foreach ($filter['options'] as $value => $optionLabel)
                    <option value="{{ $value }}">{{ $optionLabel }}</option>
                @endforeach
            </select>
            <x-lucide-chevron-down class="pointer-events-none absolute inset-y-0 right-4 my-auto size-4 stroke-[1.5] text-lh-heading" aria-hidden="true" />
        </label>
    @endforeach
</div>
