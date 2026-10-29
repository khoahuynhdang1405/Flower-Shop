<x-layouts.admin :title="__('admin.collections.create')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.collections.create')" :description="__('admin.collections.form.create_description')" />
    </x-slot:page-header>

    <x-admin.form-card :action="route('admin.collections.store')" :cancel="route('admin.collections')" :submit="__('admin.collections.form.submit_create')">
        @include('admin.collections.partials.fields')
    </x-admin.form-card>
</x-layouts.admin>
