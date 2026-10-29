<x-layouts.admin :title="__('admin.menu.collections')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.collections.title')" :description="__('admin.collections.description')">
            <x-slot:actions>
                <x-admin.create-button :href="route('admin.collections.create')">{{ __('admin.collections.create') }}</x-admin.create-button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.collections.stats.total')" :value="$stats['total']"
            :note="__('admin.collections.stats.total_note', ['published' => $stats['published'], 'hidden' => $stats['hidden']])" />
        <x-admin.stat-card :label="__('admin.collections.stats.season')" :value="$stats['season']"
            :note="__('admin.collections.stats.season_note')" />
        <x-admin.stat-card :label="__('admin.collections.stats.occasion')" :value="$stats['occasion']"
            :note="__('admin.collections.stats.occasion_note')" />
        <x-admin.stat-card :label="__('admin.collections.stats.featured')" :value="$stats['featured']"
            :note="__('admin.collections.stats.featured_note')" />
    </div>

    @if ($categories->isEmpty())
        <div class="mt-8 rounded-md border border-lh-border bg-lh-surface px-6 py-14 text-center">
            <p class="font-serif text-2xl">{{ __('admin.collections.empty_title') }}</p>
            <p class="mt-2 text-sm text-lh-muted">{{ __('admin.collections.empty_body') }}</p>
        </div>
    @else
        <div data-list-filter class="mt-8">
            <x-admin.list-toolbar :placeholder="__('admin.collections.search_placeholder')"
                class="rounded-md border border-lh-border bg-lh-surface p-6"
                :filters="[
                    ['key' => 'type', 'label' => __('admin.collections.filter_type'), 'all' => __('admin.collections.all_types'), 'options' => App\Enums\CategoryType::options()],
                    ['key' => 'status', 'label' => __('admin.collections.filter_status'), 'all' => __('admin.collections.all_statuses'), 'options' => __('admin.collections.statuses')],
                ]" />

            <ul class="mt-8 grid gap-8 md:grid-cols-2 xl:grid-cols-3" aria-live="polite">
                @foreach ($categories as $category)
                    <x-admin.collection-card :category="$category" />
                @endforeach
            </ul>
            <p data-list-empty hidden class="mt-8 rounded-md border border-lh-border bg-lh-surface px-6 py-10 text-center text-sm text-lh-muted">{{ __('admin.list.empty') }}</p>
        </div>
    @endif

    <p class="mt-8 rounded-md bg-lh-sage-50 px-6 py-6 text-sm">{{ __('admin.collections.note') }}</p>
</x-layouts.admin>
