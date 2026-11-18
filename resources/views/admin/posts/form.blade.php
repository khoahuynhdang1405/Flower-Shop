@php
    /** @var \App\Models\Post $post */
    use App\Http\Requests\Admin\StorePostRequest;

    $isNew = ! $post->exists;
    $heading = $isNew ? __('admin.posts.editor.create_title') : __('admin.posts.editor.edit_title', ['title' => $post->title]);
    $isPublished = (bool) old('is_published', $post->is_published);
    $author = $post->author?->name ?? auth('admin')->user()->name;
    $maxMegabytes = StorePostRequest::MAX_THUMBNAIL_KILOBYTES / 1024;
    $contentError = $errors->first('content');
    $card = 'rounded-md border border-lh-border bg-lh-surface';
    $tool = 'grid size-9 place-items-center rounded text-lh-heading transition-colors hover:bg-lh-sage-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary';
    $tab = 'h-9 rounded px-3.5 text-[13px] text-lh-muted transition-colors hover:text-lh-heading focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary aria-selected:bg-lh-sage-100 aria-selected:font-medium aria-selected:text-lh-heading';
    $tools = [
        'heading' => 'lucide-heading-2',
        'bold' => 'lucide-bold',
        'italic' => 'lucide-italic',
        'quote' => 'lucide-text-quote',
        'list' => 'lucide-list',
        'link' => 'lucide-link',
        'image' => 'lucide-image-plus',
    ];
@endphp

<x-layouts.admin :title="$heading">
    <x-slot:page-header>
        <x-admin.page-header :title="$heading" :description="__('admin.posts.editor.description')">
            <x-slot:actions>
                @if (! $isNew && $post->is_published)
                    <a href="{{ route('posts.show', $post->slug) }}" target="_blank" rel="noopener"
                        class="inline-flex h-12 items-center gap-2 rounded border border-lh-border bg-lh-surface px-5 text-[15px] transition-colors hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        <x-lucide-eye class="size-4 stroke-[1.5]" aria-hidden="true" />
                        {{ __('admin.posts.view_live') }}
                    </a>
                @endif
                <a href="{{ route('admin.posts') }}"
                    class="inline-flex h-12 items-center gap-2 rounded border border-lh-border bg-lh-surface px-5 text-[15px] transition-colors hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                    <x-lucide-arrow-left class="size-4 stroke-[1.5]" aria-hidden="true" />
                    {{ __('admin.posts.editor.back') }}
                </a>
            </x-slot:actions>
        </x-admin.page-header>
    </x-slot:page-header>

    <x-admin.flash class="mt-8" />

    <form method="POST" action="{{ $isNew ? route('admin.posts.store') : route('admin.posts.update', $post) }}" enctype="multipart/form-data"
        novalidate data-busy-form class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
        @csrf
        @unless ($isNew)
            @method('PUT')
        @endunless

        <div class="{{ $card }} flex flex-col gap-6 px-6 py-8 sm:px-8">
            <div>
                <x-form.input name="title" :label="__('admin.posts.editor.title')" marker :value="$post->title" required maxlength="255"
                    :placeholder="__('admin.posts.editor.title_placeholder')" />
                <p class="mt-2 text-xs break-all text-lh-muted">
                    {{ __('admin.posts.editor.link_hint', ['url' => $isNew ? route('posts.index').'/…' : route('posts.show', $post->slug)]) }}
                </p>
            </div>

            <x-form.textarea name="summary" :label="__('admin.posts.editor.summary')" :value="$post->summary" rows="3" maxlength="500" counter
                :hint="__('admin.posts.editor.summary_hint')" />

            {{-- Trình soạn Markdown: resources/js/post-editor.js (thanh công cụ, đếm chữ, xem trước, chèn ảnh). --}}
            <div data-post-editor data-preview-url="{{ route('admin.posts.preview') }}" data-upload-url="{{ route('admin.posts.images') }}"
                data-stats-template="{{ __('admin.posts.editor.stats', ['words' => ':words', 'minutes' => ':minutes']) }}"
                data-words-per-minute="{{ App\Models\Post::WORDS_PER_MINUTE }}"
                data-label-loading="{{ __('admin.posts.editor.preview_loading') }}" data-label-empty="{{ __('admin.posts.editor.preview_empty') }}"
                data-label-error="{{ __('admin.posts.editor.preview_error') }}" data-label-uploading="{{ __('admin.posts.editor.uploading') }}"
                data-label-upload-failed="{{ __('admin.posts.editor.upload_failed') }}">
                <div class="mb-2 flex flex-wrap items-end justify-between gap-3">
                    <label for="content" class="block text-sm font-medium text-lh-heading">
                        {{ __('admin.posts.editor.content') }}
                        <span aria-hidden="true">*</span>
                        <span class="sr-only">{{ __('auth.required') }}</span>
                    </label>
                    <div role="tablist" aria-label="{{ __('admin.posts.editor.content') }}" class="flex gap-1">
                        <button type="button" role="tab" id="editor-tab-write" aria-controls="editor-write" aria-selected="true" data-editor-tab="write" class="{{ $tab }}">
                            {{ __('admin.posts.editor.write') }}
                        </button>
                        <button type="button" role="tab" id="editor-tab-preview" aria-controls="editor-preview" aria-selected="false" tabindex="-1" data-editor-tab="preview" class="{{ $tab }}">
                            {{ __('admin.posts.editor.preview') }}
                        </button>
                    </div>
                </div>

                <div @class(['overflow-hidden rounded border', 'border-lh-border' => ! $contentError, 'border-lh-danger' => $contentError])>
                    <div id="editor-write" role="tabpanel" aria-labelledby="editor-tab-write" data-editor-panel="write">
                        <div role="toolbar" aria-label="{{ __('admin.posts.editor.toolbar') }}" aria-controls="content"
                            class="flex flex-wrap gap-0.5 border-b border-lh-border bg-lh-canvas px-2 py-1.5">
                            @foreach ($tools as $toolKey => $icon)
                                <button type="button" class="{{ $tool }}" data-editor-tool="{{ $toolKey }}" title="{{ __('admin.posts.editor.tools.'.$toolKey) }}">
                                    <x-dynamic-component :component="$icon" class="size-4 stroke-[1.75]" aria-hidden="true" />
                                    <span class="sr-only">{{ __('admin.posts.editor.tools.'.$toolKey) }}</span>
                                </button>
                            @endforeach
                            <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" data-editor-image-input tabindex="-1" aria-hidden="true">
                        </div>
                        <textarea id="content" name="content" rows="22" required data-editor-input spellcheck="true"
                            @if ($contentError) aria-invalid="true" aria-describedby="content-error" @endif
                            class="block w-full resize-y bg-lh-surface px-4 py-4 font-mono text-[13px] leading-relaxed text-lh-heading focus:outline-2 focus:-outline-offset-2 focus:outline-lh-primary">{{ old('content', $post->content) }}</textarea>
                    </div>
                    <div id="editor-preview" role="tabpanel" aria-labelledby="editor-tab-preview" data-editor-panel="preview" hidden
                        class="min-h-[480px] bg-lh-surface px-6 py-6">
                        <div class="post-content" data-editor-preview aria-live="polite"></div>
                    </div>
                </div>

                <div class="mt-2 flex flex-wrap justify-between gap-x-6 gap-y-1 text-xs text-lh-muted">
                    <p class="font-mono">{{ __('admin.posts.editor.guide') }}</p>
                    <p data-editor-stats aria-live="polite"></p>
                </div>
                <p data-editor-message class="mt-2 text-xs text-lh-muted" aria-live="polite"></p>
                @if ($contentError)
                    <p id="content-error" class="mt-2 flex items-start gap-1.5 text-xs text-lh-danger">
                        <x-lucide-circle-alert class="mt-px size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                        {{ $contentError }}
                    </p>
                @endif
            </div>
        </div>

        <aside class="flex flex-col gap-6 lg:sticky lg:top-24">
            <section aria-labelledby="publish-title" class="{{ $card }} px-6 py-6">
                <h2 id="publish-title" class="text-base font-semibold">{{ __('admin.posts.editor.publish_title') }}</h2>

                <fieldset class="mt-5">
                    <legend class="mb-3 text-sm font-medium text-lh-heading">{{ __('admin.posts.editor.status') }}</legend>
                    <div class="flex flex-col gap-3 text-sm">
                        <label class="flex cursor-pointer items-center gap-3">
                            <input type="radio" name="is_published" value="1" class="radio radio-primary radio-sm" @checked($isPublished)>
                            {{ __('admin.posts.editor.publish_now') }}
                        </label>
                        <label class="flex cursor-pointer items-center gap-3">
                            <input type="radio" name="is_published" value="0" class="radio radio-primary radio-sm" @checked(! $isPublished)>
                            {{ __('admin.posts.editor.save_draft') }}
                        </label>
                    </div>
                </fieldset>

                <dl class="mt-6 border-t border-lh-border pt-5 text-sm">
                    <dt class="text-[13px] text-lh-muted">{{ __('admin.posts.editor.author') }}</dt>
                    <dd class="mt-1">{{ $author }}</dd>
                </dl>

                <div class="mt-6 flex flex-col gap-3">
                    <button type="submit" data-busy-label="{{ __('auth.busy') }}"
                        class="inline-flex h-12 items-center justify-center rounded bg-lh-primary px-6 text-[15px] font-medium text-white transition-colors hover:bg-lh-primary-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary disabled:cursor-wait disabled:opacity-80">
                        <span data-button-label>{{ __('admin.posts.editor.save') }}</span>
                    </button>
                    <a href="{{ route('admin.posts') }}"
                        class="inline-flex h-12 items-center justify-center rounded border border-lh-border px-6 text-[15px] transition-colors hover:bg-lh-canvas focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lh-primary">
                        {{ __('admin.posts.editor.cancel') }}
                    </a>
                </div>
            </section>

            <section aria-labelledby="thumbnail-title" class="{{ $card }} px-6 py-6">
                <h2 id="thumbnail-title" class="text-base font-semibold">{{ __('admin.posts.editor.thumbnail') }}</h2>

                <div class="mt-4">
                    @if ($post->thumbnail_url)
                        <img src="{{ $post->thumbnail_url }}" alt="{{ __('admin.posts.thumbnail_alt', ['title' => $post->title]) }}"
                            class="aspect-[16/10] w-full rounded bg-lh-sage-100 object-cover">
                    @endif
                </div>

                <label for="thumbnail"
                    class="mt-4 flex cursor-pointer items-center justify-center gap-2 rounded border border-dashed border-lh-sage-200 bg-lh-cream px-4 py-4 text-sm font-medium transition-colors hover:bg-lh-sage-50 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-lh-primary">
                    <x-lucide-image-plus class="size-5 stroke-[1.5] text-lh-primary" aria-hidden="true" />
                    {{ __('admin.posts.editor.choose_thumbnail') }}
                    <input id="thumbnail" name="thumbnail" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                        data-image-preview data-selected-template=":count" aria-describedby="thumbnail-hint">
                </label>
                <ul data-image-preview-list class="mt-3 grid grid-cols-1 empty:hidden" role="list"></ul>
                <p data-image-preview-count class="sr-only" aria-live="polite"></p>
                <p id="thumbnail-hint" class="mt-2 text-xs text-lh-muted">{{ __('admin.posts.editor.thumbnail_hint', ['mb' => $maxMegabytes]) }}</p>

                @error('thumbnail')
                    <p class="mt-2 flex items-start gap-1.5 text-xs text-lh-danger">
                        <x-lucide-circle-alert class="mt-px size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                        {{ $message }}
                    </p>
                @enderror

                @if ($post->thumbnail)
                    <div class="mt-4">
                        <x-form.checkbox name="remove_thumbnail">{{ __('admin.posts.editor.remove_thumbnail') }}</x-form.checkbox>
                    </div>
                @endif
            </section>
        </aside>
    </form>

    @unless ($isNew)
        <div class="mt-6 flex justify-end">
            <x-admin.delete-button :action="route('admin.posts.destroy', $post)" :label="__('admin.list.delete_item', ['name' => $post->title])"
                :confirm="__('admin.posts.confirm_delete', ['title' => $post->title])" />
        </div>
    @endunless
</x-layouts.admin>
