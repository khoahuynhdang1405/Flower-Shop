<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Customer\AdminCustomerFilters;
use App\Enums\CustomerGroup;
use App\Enums\CustomerSort;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query string của trang Admin › Khách hàng: ?q= (tìm kiếm) &group= (nhóm khách) &sort= (sắp xếp).
 */
class ListCustomersRequest extends FormRequest
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
            'group' => ['nullable', Rule::enum(CustomerGroup::class)],
            'sort' => ['nullable', Rule::enum(CustomerSort::class)],
        ];
    }

    public function toFilters(): AdminCustomerFilters
    {
        $search = trim((string) $this->validated('q'));

        return new AdminCustomerFilters(
            search: $search === '' ? null : $search,
            group: CustomerGroup::tryFrom((string) $this->validated('group')),
            sort: CustomerSort::tryFrom((string) $this->validated('sort')) ?? CustomerSort::RecentlyUpdated,
        );
    }
}
