<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rules\Unique;

/**
 * Cùng luật với StorePromotionRequest; mã được trùng với chính ưu đãi đang sửa.
 */
class UpdatePromotionRequest extends StorePromotionRequest
{
    protected function uniqueCode(): Unique
    {
        return parent::uniqueCode()->ignore($this->route('promotion'));
    }
}
