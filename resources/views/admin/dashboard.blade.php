<x-layouts.admin :title="__('admin.menu.dashboard')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.dashboard.title')" :description="$description">
            <x-slot:actions>
                {{-- TODO: mở bộ chọn ngày để xem số liệu theo ngày. --}}
                <button type="button"
                    class="inline-flex h-12 items-center gap-4 rounded border border-lh-heading px-6 text-[15px] text-lh-heading transition-colors hover:bg-lh-surface focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    {{ $todayLabel }}
                    <x-lucide-calendar-days class="size-5 stroke-[1.5]" aria-hidden="true" />
                </button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    {{-- Nội dung Tổng quan (thẻ số liệu, biểu đồ, bảng) sẽ làm ở bước sau. --}}
</x-layouts.admin>
