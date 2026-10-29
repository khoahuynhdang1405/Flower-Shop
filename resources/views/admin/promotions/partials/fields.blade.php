{{-- Các ô của form Thêm/Sửa ưu đãi ($promotion: App\Models\Promotion). Số tiền tính bằng VNĐ. --}}
<x-form.input name="code" :label="__('admin.promotions.form.code')" marker :value="$promotion->code"
    :hint="__('admin.promotions.form.code_hint')" placeholder="CHAOHOA" required maxlength="50"
    autocapitalize="characters" spellcheck="false" class="uppercase" />

<x-form.select name="discount_type" :label="__('admin.promotions.form.type')" marker
    :options="App\Enums\DiscountType::options()" :value="$promotion->discount_type" required />

<x-form.input name="discount_value" :label="__('admin.promotions.form.value')" marker :value="$promotion->discount_value"
    :hint="__('admin.promotions.form.value_hint')" inputmode="numeric" required />

<x-form.input name="max_discount_amount" :label="__('admin.promotions.form.max')" :value="$promotion->max_discount_amount"
    :hint="__('admin.promotions.form.max_hint')" inputmode="numeric" />

<x-form.input name="min_order_amount" :label="__('admin.promotions.form.min')" :value="$promotion->min_order_amount"
    :hint="__('admin.promotions.form.min_hint')" inputmode="numeric" />

<div class="flex items-end pb-3">
    <x-form.checkbox name="is_active" :checked="$promotion->is_active">{{ __('admin.promotions.form.is_active') }}</x-form.checkbox>
</div>
