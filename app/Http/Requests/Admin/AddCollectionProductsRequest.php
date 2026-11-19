<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Thêm các sản phẩm đã tích vào bộ sưu tập. Chỉ Theo mùa / Theo dịp; Kiểu hoa đổi ở form sản phẩm.
 */
class AddCollectionProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $collection = $this->route('collection');

        return auth('admin')->check() && $collection instanceof Category && $collection->type->allowsManualProducts();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_ids.required' => __('admin.collection_products.choose_required'),
            'product_ids.min' => __('admin.collection_products.choose_required'),
            'product_ids.*' => __('admin.collection_products.product_missing'),
        ];
    }

    /**
     * @return list<int>
     */
    public function productIds(): array
    {
        return array_map('intval', array_values($this->validated('product_ids')));
    }
}
