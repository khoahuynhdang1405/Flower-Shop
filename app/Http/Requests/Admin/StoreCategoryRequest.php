<?php

namespace App\Http\Requests\Admin;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Validator;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $name = is_string($this->name) ? trim($this->name) : $this->name;

        $this->merge([
            'name' => $name,
            'slug' => is_string($name) ? Str::slug($name) : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Lỗi của slug hiển thị dưới ô Tên, vì form không có ô slug.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $errors = $validator->errors();

                if ($errors->has('slug') && ! $errors->has('name')) {
                    $errors->add('name', $errors->first('slug'));
                }

                $errors->forget('slug');
            },
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', $this->uniqueSlug()],
            'type' => ['required', Rule::enum(CategoryType::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('admin.collections.form.name_required'),
            'slug.required' => __('admin.collections.form.name_required'),
            'slug.unique' => __('admin.collections.form.slug_taken'),
            'type.required' => __('admin.collections.form.type_required'),
            'type.enum' => __('admin.collections.form.type_required'),
        ];
    }

    protected function uniqueSlug(): Unique
    {
        return Rule::unique(Category::class, 'slug');
    }
}
