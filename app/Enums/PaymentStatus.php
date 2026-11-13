<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Trạng thái thanh toán (orders.payment_status).
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return __('order.payment_statuses.'.$this->value);
    }
}
