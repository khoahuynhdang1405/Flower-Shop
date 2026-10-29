<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rules\Unique;

/**
 * Cùng luật với StoreCategoryRequest; slug được trùng với chính bộ sưu tập đang sửa.
 */
class UpdateCategoryRequest extends StoreCategoryRequest
{
    protected function uniqueSlug(): Unique
    {
        return parent::uniqueSlug()->ignore($this->route('collection'));
    }
}
