<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\DTOs\Order\AdminOrderFilters;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query string của trang Admin › Đơn hàng: ?status= (tab) &q= (tìm kiếm) &payment= (trạng thái thanh toán).
 */
class ListOrdersRequest extends FormRequest
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
            'status' => ['nullable', Rule::in(array_map(fn (OrderStatus $tab): string => $tab->value, OrderStatus::adminTabs()))],
            'q' => ['nullable', 'string', 'max:100'],
            'payment' => ['nullable', Rule::enum(PaymentStatus::class)],
        ];
    }

    public function toFilters(): AdminOrderFilters
    {
        $search = trim((string) $this->validated('q'));

        return new AdminOrderFilters(
            tab: OrderStatus::tryFrom((string) $this->validated('status')),
            search: $search === '' ? null : $search,
            payment: PaymentStatus::tryFrom((string) $this->validated('payment')),
        );
    }
}
