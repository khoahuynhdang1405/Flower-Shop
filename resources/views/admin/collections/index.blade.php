@php
    /*
     * TODO: dữ liệu mẫu theo docs/designs/admin/04-bo-suu-tap.png — chỉ dựng giao diện.
     * Khi làm backend: ảnh bìa, khẩu hiệu, trạng thái nháp, thời gian áp dụng và quan hệ nhiều–nhiều
     * với sản phẩm chưa có trong DB (docs/design-vs-db-gaps.md #2).
     */
    $collections = [
        ['name' => 'Dịu dàng mùa thu', 'image' => 'diu-dang-mua-thu', 'type' => 'season', 'status' => 'published', 'tagline' => 'Hồng phấn, cam đào và những sắc thu dịu êm.', 'products' => 12, 'period' => '01/10–30/11/2026'],
        ['name' => 'Một ngày sinh nhật', 'image' => 'mot-ngay-sinh-nhat', 'type' => 'occasion', 'status' => 'published', 'tagline' => 'Thêm một chút rực rỡ cho tuổi mới.', 'products' => 10, 'period' => null],
        ['name' => 'Một lời yêu thương', 'image' => 'mot-loi-yeu-thuong', 'type' => 'occasion', 'status' => 'published', 'tagline' => 'Thay lời trái tim muốn nói, gửi người mình thương.', 'products' => 14, 'period' => null],
        ['name' => 'Một lời chúc mừng', 'image' => 'mot-loi-chuc-mung', 'type' => 'occasion', 'status' => 'published', 'tagline' => 'Cùng bạn mừng những điều thật tốt đẹp.', 'products' => 8, 'period' => null],
        ['name' => 'Một ngày thật bình thường', 'image' => 'mot-ngay-that-binh-thuong', 'type' => 'occasion', 'status' => 'published', 'tagline' => 'Vì bạn xứng đáng được yêu, không cần một lý do.', 'products' => 6, 'period' => null],
        ['name' => 'Ấm áp mùa đông', 'image' => 'am-ap-mua-dong', 'type' => 'season', 'status' => 'draft', 'tagline' => 'Một lời hẹn ấm áp cho những ngày cuối năm.', 'products' => 5, 'period' => null, 'planned_at' => '01/12/2026'],
    ];

    $all = collect($collections);
    $featured = $all->first();
@endphp

<x-layouts.admin :title="__('admin.menu.collections')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.collections.title')" :description="__('admin.collections.description')">
            <x-slot:actions>
                {{-- TODO: form thêm bộ sưu tập. --}}
                <x-admin.create-button>{{ __('admin.collections.create') }}</x-admin.create-button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.collections.stats.total')" :value="$all->count()"
            :note="__('admin.collections.stats.total_note', ['published' => $all->where('status', 'published')->count(), 'drafts' => $all->where('status', 'draft')->count()])" />
        <x-admin.stat-card :label="__('admin.collections.stats.season')" :value="$all->where('type', 'season')->count()"
            :note="__('admin.collections.stats.season_note')" />
        <x-admin.stat-card :label="__('admin.collections.stats.occasion')" :value="$all->where('type', 'occasion')->count()"
            :note="__('admin.collections.stats.occasion_note')" />
        <x-admin.stat-card :label="__('admin.collections.stats.featured')" :value="$featured['products']"
            :note="__('admin.collections.stats.featured_note', ['name' => $featured['name']])" />
    </div>

    <div data-list-filter class="mt-8">
        <x-admin.list-toolbar :placeholder="__('admin.collections.search_placeholder')"
            class="rounded-md border border-lh-border bg-lh-surface p-6"
            :filters="[
                ['key' => 'type', 'label' => __('admin.collections.filter_type'), 'all' => __('admin.collections.all_types'), 'options' => __('admin.collections.types')],
                ['key' => 'status', 'label' => __('admin.collections.filter_status'), 'all' => __('admin.collections.all_statuses'), 'options' => __('admin.collections.statuses')],
            ]" />

        <ul class="mt-8 grid gap-8 md:grid-cols-2 xl:grid-cols-3" aria-live="polite">
            @foreach ($collections as $collection)
                <x-admin.collection-card :collection="$collection" />
            @endforeach
        </ul>
        <p data-list-empty hidden class="mt-8 rounded-md border border-lh-border bg-lh-surface px-6 py-10 text-center text-sm text-lh-muted">{{ __('admin.list.empty') }}</p>
    </div>

    <p class="mt-8 rounded-md bg-lh-sage-50 px-6 py-6 text-sm">{{ __('admin.collections.note') }}</p>
</x-layouts.admin>
