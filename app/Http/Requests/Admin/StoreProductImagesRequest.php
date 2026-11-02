<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\ProductImage\UploadProductImagesData;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Upload nhiều ảnh (jpg/png/webp, tối đa 4 MB/ảnh, 10 ảnh/lượt) cho một sản phẩm.
 */
class StoreProductImagesRequest extends FormRequest
{
    public const MAX_FILES = 10;

    public const MAX_KILOBYTES = 4096;

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
        return [
            'images' => ['required', 'array', 'max:'.self::MAX_FILES],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_KILOBYTES],
            'product_size_id' => ['nullable', 'integer', $this->sizeOfThisProduct()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.required' => __('admin.product_images.form.images_required'),
            'images.max' => __('admin.product_images.form.images_max', ['max' => self::MAX_FILES]),
            'images.*.image' => __('admin.product_images.form.image_type'),
            'images.*.mimes' => __('admin.product_images.form.image_type'),
            'images.*.max' => __('admin.product_images.form.image_size', ['max' => self::MAX_KILOBYTES / 1024]),
            'images.*.uploaded' => __('admin.product_images.form.image_size', ['max' => self::MAX_KILOBYTES / 1024]),
            'product_size_id.exists' => __('admin.product_images.form.size_invalid'),
            'product_size_id.integer' => __('admin.product_images.form.size_invalid'),
        ];
    }

    public function toData(): UploadProductImagesData
    {
        return new UploadProductImagesData(
            productId: $this->product()->id,
            productSizeId: $this->filled('product_size_id') ? $this->integer('product_size_id') : null,
            files: array_values($this->file('images', [])),
        );
    }

    /**
     * size_id phải là size của chính sản phẩm đang sửa, không gắn nhầm sang sản phẩm khác.
     */
    protected function sizeOfThisProduct(): Exists
    {
        return Rule::exists('product_sizes', 'id')->where('product_id', $this->product()->id);
    }

    protected function product(): Product
    {
        return $this->route('product');
    }
}
