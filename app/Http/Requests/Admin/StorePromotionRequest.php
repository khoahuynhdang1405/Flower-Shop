<?php

namespace App\Http\Requests\Admin;

use App\Enums\DiscountType;
use App\Models\Promotion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StorePromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * Mã viết hoa, bỏ khoảng trắng; số tiền bỏ dấu chấm/phẩy ngăn cách ("100.000" → 100000).
     * Giảm theo số tiền thì không có mức giảm tối đa.
     */
    protected function prepareForValidation(): void
    {
        $toNumber = fn (mixed $value): mixed => is_string($value) ? preg_replace('/[\s.,]/', '', $value) : $value;

        $this->merge([
            'code' => is_string($this->code) ? mb_strtoupper(preg_replace('/\s+/', '', $this->code)) : $this->code,
            'discount_value' => $toNumber($this->discount_value),
            'min_order_amount' => $toNumber($this->min_order_amount) ?: 0,
            'max_discount_amount' => $this->discount_type === DiscountType::Fixed->value ? null : ($toNumber($this->max_discount_amount) ?: null),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isPercentage = $this->discount_type === DiscountType::Percentage->value;

        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9]+$/', $this->uniqueCode()],
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            'discount_value' => $isPercentage
                ? ['required', 'integer', 'between:1,100']
                : ['required', 'integer', 'min:1000', 'max:9999999999'],
            'min_order_amount' => ['integer', 'min:0', 'max:9999999999'],
            'max_discount_amount' => ['nullable', 'integer', 'min:1000', 'max:9999999999'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => __('admin.promotions.form.code_required'),
            'code.regex' => __('admin.promotions.form.code_format'),
            'code.unique' => __('admin.promotions.form.code_taken'),
            'discount_type.required' => __('admin.promotions.form.type_required'),
            'discount_type.enum' => __('admin.promotions.form.type_required'),
            'discount_value.required' => __('admin.promotions.form.value_required'),
            'discount_value.between' => __('admin.promotions.form.percent_range'),
            'discount_value.min' => __('admin.promotions.form.amount_min'),
            'discount_value.integer' => __('admin.promotions.form.number_only'),
            'min_order_amount.integer' => __('admin.promotions.form.number_only'),
            'max_discount_amount.integer' => __('admin.promotions.form.number_only'),
            'max_discount_amount.min' => __('admin.promotions.form.amount_min'),
        ];
    }

    protected function uniqueCode(): Unique
    {
        return Rule::unique(Promotion::class, 'code');
    }
}
