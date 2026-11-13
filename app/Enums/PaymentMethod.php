<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Phương thức thanh toán (orders.payment_method).
 */
enum PaymentMethod: string
{
    case Cod = 'cod';
    case Vnpay = 'vnpay';
    case Momo = 'momo';
    case BankTransfer = 'bank_transfer';

    /**
     * Thanh toán qua cổng trực tuyến: đặt xong chuyển khách sang trang của cổng.
     */
    public function isOnline(): bool
    {
        return in_array($this, [self::Vnpay, self::Momo], true);
    }

    public function label(): string
    {
        return __('order.payment_methods.'.$this->value);
    }
}
