<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Ảnh chèn vào nội dung bài (gửi bằng fetch từ khung soạn thảo, trả lỗi dạng JSON).
 */
class UploadPostImageRequest extends FormRequest
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
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.StorePostRequest::MAX_THUMBNAIL_KILOBYTES],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $tooLarge = __('admin.posts.form.image_size', ['max' => StorePostRequest::MAX_THUMBNAIL_KILOBYTES / 1024]);

        return [
            'image.required' => __('admin.posts.form.image_type'),
            'image.image' => __('admin.posts.form.image_type'),
            'image.mimes' => __('admin.posts.form.image_type'),
            'image.max' => $tooLarge,
            'image.uploaded' => $tooLarge,
        ];
    }
}
