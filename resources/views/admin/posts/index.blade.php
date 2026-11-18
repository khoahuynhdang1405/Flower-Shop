@php
    /**
     * @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\Post> $posts
     * @var array{total: int, published: int, drafts: int, updatedThisWeek: int} $stats
     * @var \App\Models\Post|null $latestDraft
     * @var \App\DTOs\Post\AdminPostFilters $filters
     */
    $isFiltered = $filters->search !== null || $filters->published !== null;
    $status = match ($filters->published) {
        true => 'published',
        false => 'draft',
        null => null,
    };
    $cell = 'px-4 py-5 align-middle first:pl-6 last:pr-6';
    $editLink = 'inline-flex items-center gap-2 rounded text-sm whitespace-nowrap transition-colors hover:text-lh-primary focus-visible:outline-2 focus-visible:outline-lh-primary';
@endphp

<x-layouts.admin :title="__('admin.menu.posts')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.posts.title')" :description="__('admin.posts.description')">
            <x-slot:actions>
                <x-admin.create-button :href="route('admin.posts.create')">{{ __('admin.posts.create') }}</x-admin.create-button>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card :label="__('admin.posts.stats.total')" :value="$stats['total']" :note="__('admin.posts.stats.total_note')" />
        <x-admin.stat-card :label="__('admin.posts.stats.published')" :value="$stats['published']" :note="__('admin.posts.stats.published_note')" />
        <x-admin.stat-card :label="__('admin.posts.stats.drafts')" :value="$stats['drafts']" :note="__('admin.posts.stats.drafts_note')" />
        <x-admin.stat-card :label="__('admin.posts.stats.updated')" :value="$stats['updatedThisWeek']" :note="__('admin.posts.stats.updated_note')" />
    </div>

    <section aria-labelledby="posts-table-title" class="mt-8 overflow-hidden rounded-md border border-lh-border bg-lh-surface">
        <div class="px-6 pt-8">
            <h2 id="posts-table-title" class="text-xl font-semibold">{{ __('admin.posts.table_title') }}</h2>
            <p class="mt-2 text-[13px] text-lh-muted">{{ __('admin.posts.table_description') }}</p>

            <form method="GET" action="{{ route('admin.posts') }}" role="search" class="mt-10 mb-6">
                <x-admin.list-toolbar server :search="$filters->search" :placeholder="__('admin.posts.search_placeholder')"
                    :filters="[[
                        'key' => 'status',
                        'label' => __('admin.posts.filter_status'),
                        'all' => __('admin.posts.all_statuses'),
                        'options' => __('admin.posts.statuses'),
                        'value' => $status,
                    ]]" />
            </form>
        </div>

        @if ($posts->isEmpty())
            <div class="border-t border-lh-border px-6 py-14 text-center">
                @if ($isFiltered)
                    <p class="text-sm text-lh-muted">{{ __('admin.posts.empty_filtered') }}</p>
                    <a href="{{ route('admin.posts') }}"
                        class="mt-4 inline-block rounded text-sm font-medium text-lh-primary underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        {{ __('admin.posts.clear_filters') }}
                    </a>
                @else
                    <p class="font-serif text-2xl">{{ __('admin.posts.empty_title') }}</p>
                    <p class="mt-2 text-sm text-lh-muted">{{ __('admin.posts.empty_body') }}</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left">
                    <thead class="bg-lh-canvas text-[13px] text-lh-muted">
                        <tr>
                            @foreach (['post', 'author', 'updated', 'status', 'actions'] as $column)
                                <th scope="col" class="px-4 py-5 font-normal whitespace-nowrap first:pl-6 last:pr-6">{{ __('admin.posts.columns.'.$column) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr class="border-t border-lh-border">
                                <td class="{{ $cell }}">
                                    <div class="flex items-center gap-4">
                                        @if ($post->thumbnail_url)
                                            <img src="{{ $post->thumbnail_url }}" alt="{{ __('admin.posts.thumbnail_alt', ['title' => $post->title]) }}" loading="lazy"
                                                class="h-[52px] w-16 shrink-0 rounded bg-lh-sage-100 object-cover">
                                        @else
                                            <x-flower-image :name="'goc-nho-'.$post->slug" class="h-[52px] w-16 shrink-0 rounded" />
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-[15px] font-semibold">{{ $post->title }}</p>
                                            <p class="mt-1.5 text-xs text-lh-muted">{{ trans_choice('posts.reading_time', $post->reading_minutes, ['minutes' => $post->reading_minutes]) }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="{{ $cell }} text-[15px] whitespace-nowrap">{{ $post->author->name }}</td>
                                <td class="{{ $cell }} text-[15px] tabular-nums whitespace-nowrap">{{ $post->updated_at->format('d/m/Y') }}</td>
                                <td class="{{ $cell }}">
                                    <x-admin.badge :tone="$post->is_published ? 'success' : 'neutral'">
                                        {{ __('admin.posts.statuses.'.($post->is_published ? 'published' : 'draft')) }}
                                    </x-admin.badge>
                                </td>
                                <td class="{{ $cell }}">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="{{ $editLink }}"
                                        aria-label="{{ __('admin.list.edit_item', ['name' => $post->title]) }}">
                                        <x-lucide-pencil class="size-[18px] stroke-[1.5]" aria-hidden="true" />
                                        {{ __('admin.list.edit') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-4 border-t border-lh-border px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[13px] text-lh-muted">{{ __('admin.posts.showing', ['shown' => $posts->count(), 'total' => $posts->total()]) }}</p>
                <x-admin.pagination :paginator="$posts" />
            </div>
        @endif
    </section>

    @if ($latestDraft)
        <aside class="mt-8 flex flex-col gap-5 rounded-md bg-lh-sage-50 px-6 py-7 sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <p class="font-serif text-2xl">{{ __('admin.posts.reminder.title') }}</p>
                <p class="mt-2 text-sm text-lh-muted">{{ __('admin.posts.reminder.body', ['title' => $latestDraft->title]) }}</p>
            </div>
            <a href="{{ route('admin.posts.edit', $latestDraft) }}"
                class="inline-flex h-12 shrink-0 items-center justify-center rounded border border-lh-primary-dark bg-lh-surface px-6 text-[15px] text-lh-heading transition-colors
                    hover:bg-lh-primary hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                {{ __('admin.posts.reminder.action') }}
            </a>
        </aside>
    @endif
</x-layouts.admin>
