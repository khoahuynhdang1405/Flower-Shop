<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Sửa bài: cùng luật với StorePostRequest, nhưng slug giữ nguyên nên không kiểm tra trùng tiêu đề.
 */
class UpdatePostRequest extends StorePostRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'slug' => ['nullable'],
            'remove_thumbnail' => ['boolean'],
        ];
    }
}
