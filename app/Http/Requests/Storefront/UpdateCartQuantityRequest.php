<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use App\Actions\Cart\AddFlowerToCartAction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Nút − / + của một dòng trong giỏ: gửi số lượng mới.
 */
class UpdateCartQuantityRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1', 'max:'.AddFlowerToCartAction::MAX_QUANTITY],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['quantity' => __('cart.attributes.quantity')];
    }
}
