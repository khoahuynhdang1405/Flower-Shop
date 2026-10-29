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
