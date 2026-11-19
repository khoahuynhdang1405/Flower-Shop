<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Bỏ một sản phẩm khỏi bộ sưu tập. Chỉ Theo mùa / Theo dịp; Kiểu hoa đổi ở form sản phẩm.
 */
class RemoveCollectionProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $collection = $this->route('collection');

        return auth('admin')->check() && $collection instanceof Category && $collection->type->allowsManualProducts();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
