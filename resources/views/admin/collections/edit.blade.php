<x-layouts.admin :title="__('admin.collections.edit', ['name' => $category->name])">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.collections.edit', ['name' => $category->name])" :description="__('admin.collections.form.edit_description')" />
    </x-slot:page-header>

    <x-admin.form-card :action="route('admin.collections.update', $category)" method="PUT" :cancel="route('admin.collections')"
        :submit="__('admin.list.save')">
        @include('admin.collections.partials.fields')
    </x-admin.form-card>
</x-layouts.admin>
