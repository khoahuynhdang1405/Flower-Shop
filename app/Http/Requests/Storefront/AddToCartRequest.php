<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use App\Actions\Cart\AddFlowerToCartAction;
use App\DTOs\Cart\AddFlowerToCartData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form "Thêm vào giỏ" ở trang chi tiết: sản phẩm, size (radio), số lượng, quà kèm đã tích.
 * Sản phẩm/size/quà còn bán không được kiểm tra ở đây mà ở Action (đọc lại DB).
 */
class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer'],
            'size' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.AddFlowerToCartAction::MAX_QUANTITY],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['quantity' => __('cart.attributes.quantity')];
    }

    public function toFlowerData(): AddFlowerToCartData
    {
        return new AddFlowerToCartData(
            productId: $this->integer('product_id'),
            sizeId: $this->filled('size') ? $this->integer('size') : null,
            quantity: $this->integer('quantity'),
        );
    }

    /**
     * @return list<int>
     */
    public function addonIds(): array
    {
        return array_map('intval', array_values($this->array('addons')));
    }
}
