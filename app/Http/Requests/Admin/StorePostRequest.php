<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Post\PostData;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

/**
 * Viết bài mới. Slug lấy từ tiêu đề nên tiêu đề không được trùng (sau khi bỏ dấu) với bài khác;
 * lỗi slug hiển thị dưới ô Tiêu đề vì form không có ô slug.
 */
class StorePostRequest extends FormRequest
{
    public const MAX_THUMBNAIL_KILOBYTES = 4096;

    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $title = is_string($this->title) ? trim($this->title) : $this->title;

        $this->merge([
            'title' => $title,
            'summary' => is_string($this->summary) ? trim($this->summary) : $this->summary,
            'slug' => is_string($title) ? Str::slug($title) : null,
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:60000'],
            'is_published' => ['boolean'],
            'thumbnail' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.self::MAX_THUMBNAIL_KILOBYTES],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => __('admin.posts.form.title_required'),
            'slug.required' => __('admin.posts.form.title_required'),
            'slug.unique' => __('admin.posts.form.title_taken'),
            'summary.max' => __('admin.posts.form.summary_max'),
            'content.required' => __('admin.posts.form.content_required'),
            'content.max' => __('admin.posts.form.content_max'),
            'thumbnail.image' => __('admin.posts.form.image_type'),
            'thumbnail.mimes' => __('admin.posts.form.image_type'),
            'thumbnail.max' => __('admin.posts.form.image_size', ['max' => self::MAX_THUMBNAIL_KILOBYTES / 1024]),
            'thumbnail.uploaded' => __('admin.posts.form.image_size', ['max' => self::MAX_THUMBNAIL_KILOBYTES / 1024]),
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $errors = $validator->errors();

                if ($errors->has('slug') && ! $errors->has('title')) {
                    $errors->add('title', $errors->first('slug'));
                }

                $errors->forget('slug');
            },
        ];
    }

    public function toData(): PostData
    {
        return new PostData(
            title: $this->validated('title'),
            summary: $this->validated('summary') ?: null,
            content: $this->validated('content'),
            isPublished: (bool) $this->validated('is_published'),
            thumbnail: $this->file('thumbnail'),
            removeThumbnail: $this->boolean('remove_thumbnail'),
        );
    }
}
