<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Đổi size của một ảnh (để trống = ảnh chung).
 */
class UpdateProductImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'product_size_id' => ['nullable', 'integer', Rule::exists('product_sizes', 'id')->where('product_id', $product->id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_size_id.exists' => __('admin.product_images.form.size_invalid'),
            'product_size_id.integer' => __('admin.product_images.form.size_invalid'),
        ];
    }

    public function productSizeId(): ?int
    {
        return $this->filled('product_size_id') ? $this->integer('product_size_id') : null;
    }
}
