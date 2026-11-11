<?php

declare(strict_types=1);

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Trang xác nhận / thanh toán lại của một đơn: chỉ trình duyệt vừa đặt đơn đó mới xem được
 * (mã đơn ngắn, đoán được, trong khi trang có tên, SĐT, địa chỉ người nhận).
 */
class PlacedOrderRequest extends FormRequest
{
    public const SESSION_KEY = 'placed_orders';

    public function authorize(): bool
    {
        return in_array($this->orderId(), $this->session()->get(self::SESSION_KEY, []), true);
    }

    /**
     * Trả 404 thay vì 403 để không lộ mã đơn nào đang tồn tại.
     */
    protected function failedAuthorization(): never
    {
        abort(404);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    public function orderId(): int
    {
        return (int) $this->route('order');
    }
}
