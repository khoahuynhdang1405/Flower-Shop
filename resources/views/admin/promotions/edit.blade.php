<x-layouts.admin :title="__('admin.promotions.edit', ['code' => $promotion->code])">
    <x-slot:page-header>
        <x-admin.page-header :title="__('admin.promotions.edit', ['code' => $promotion->code])" :description="__('admin.promotions.form.edit_description')" />
    </x-slot:page-header>

    <x-admin.form-card :action="route('admin.promotions.update', $promotion)" method="PUT" :cancel="route('admin.promotions')"
        :submit="__('admin.list.save')">
        @include('admin.promotions.partials.fields')
    </x-admin.form-card>
</x-layouts.admin>
