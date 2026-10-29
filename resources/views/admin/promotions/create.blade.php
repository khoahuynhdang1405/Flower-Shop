<x-layouts.admin :title="__('admin.promotions.create')">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.promotions.create')" :description="__('admin.promotions.form.create_description')" />
    </x-slot:page-header>

    <x-admin.form-card :action="route('admin.promotions.store')" :cancel="route('admin.promotions')" :submit="__('admin.promotions.form.submit_create')">
        @include('admin.promotions.partials.fields')
    </x-admin.form-card>
</x-layouts.admin>
