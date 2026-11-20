{{-- Các ô của form Thêm/Sửa bộ sưu tập ($category: App\Models\Category). --}}
<x-form.input name="name" :label="__('admin.collections.form.name')" marker :value="$category->name"
    :placeholder="__('admin.collections.form.name_placeholder')" required maxlength="255" />

<x-form.select name="type" :label="__('admin.collections.form.type')" marker :options="App\Enums\CategoryType::options()"
    :value="$category->type" required />

<div class="flex items-end pb-3">
    <x-form.checkbox name="is_active" :checked="$category->is_active">{{ __('admin.collections.form.is_active') }}</x-form.checkbox>
</div>

<div class="md:col-span-2">
    <x-form.textarea name="description" :label="__('admin.collections.form.description')" :value="$category->description"
        :hint="__('admin.collections.form.description_hint')" maxlength="2000" />
</div>

{{-- Ảnh chính: lưu vào storage/app/public/collections (categories.cover_image). --}}
@php
    $coverError = $errors->first('cover_image');
    $coverMegabytes = App\Http\Requests\Admin\StoreCategoryRequest::MAX_COVER_KILOBYTES / 1024;
@endphp
<div class="md:col-span-2">
    <p class="text-sm font-medium text-lh-heading">{{ __('admin.collections.form.cover') }}</p>

    <div class="mt-3 grid gap-4 sm:grid-cols-[minmax(0,280px)_minmax(0,1fr)] sm:items-start">
        @if ($category->cover_url)
            <img src="{{ $category->cover_url }}" alt="{{ __('admin.collections.form.current_cover_alt', ['name' => $category->name]) }}"
                class="aspect-[491/253] w-full rounded bg-lh-sage-100 object-cover">
        @endif

        <div @class(['sm:col-span-2' => ! $category->cover_url])>
            <label for="cover_image"
                @class([
                    'flex cursor-pointer items-center justify-center gap-2 rounded border border-dashed bg-lh-cream px-4 py-5 text-sm font-medium transition-colors hover:bg-lh-sage-50',
                    'has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-lh-primary',
                    'border-lh-sage-200' => ! $coverError,
                    'border-lh-danger' => $coverError,
                ])>
                <x-lucide-image-plus class="size-5 stroke-[1.5] text-lh-primary" aria-hidden="true" />
                {{ __('admin.collections.form.choose_cover') }}
                <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only"
                    data-image-preview data-selected-template=":count"
                    aria-describedby="cover_image-hint{{ $coverError ? ' cover_image-error' : '' }}" @if ($coverError) aria-invalid="true" @endif>
            </label>
            <ul data-image-preview-list class="mt-3 grid max-w-[280px] grid-cols-1 empty:hidden" role="list"></ul>
            <p data-image-preview-count class="sr-only" aria-live="polite"></p>
            <p id="cover_image-hint" class="mt-2 text-xs text-lh-muted">{{ __('admin.collections.form.cover_hint', ['mb' => $coverMegabytes]) }}</p>

            @if ($coverError)
                <p id="cover_image-error" class="mt-2 flex items-start gap-1.5 text-xs text-lh-danger">
                    <x-lucide-circle-alert class="mt-px size-3.5 shrink-0 stroke-[1.5]" aria-hidden="true" />
                    {{ $coverError }}
                </p>
            @endif

            @if ($category->cover_image)
                <div class="mt-4">
                    <x-form.checkbox name="remove_cover">{{ __('admin.collections.form.remove_cover') }}</x-form.checkbox>
                </div>
            @endif
        </div>
    </div>
</div>
