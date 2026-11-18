<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Post\AdminPostFilters;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Query string của trang Admin › Chuyện Hoa: ?q= (tìm tiêu đề) &status=published|draft.
 */
class ListPostsRequest extends FormRequest
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
            'status' => ['nullable', 'in:published,draft'],
        ];
    }

    public function toFilters(): AdminPostFilters
    {
        $search = trim((string) $this->validated('q'));

        return new AdminPostFilters(
            search: $search === '' ? null : $search,
            published: match ($this->validated('status')) {
                'published' => true,
                'draft' => false,
                default => null,
            },
        );
    }
}
