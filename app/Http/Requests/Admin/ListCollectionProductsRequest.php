<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Query string của trang Bộ sưu tập › Sản phẩm: ?q= tìm sản phẩm để thêm.
 */
class ListCollectionProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function search(): ?string
    {
        $search = trim((string) $this->validated('q'));

        return $search === '' ? null : $search;
    }
}
