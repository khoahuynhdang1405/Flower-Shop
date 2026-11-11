<?php

declare(strict_types=1);

namespace App\DTOs\Payment;

use App\Enums\PaymentConfirmationOutcome;
use App\Models\Order;

/**
 * Kết quả xử lý thông báo thanh toán; order null khi không xác định được đơn (chữ ký sai, mã lạ).
 */
final readonly class PaymentConfirmationData
{
    public function __construct(
        public PaymentConfirmationOutcome $outcome,
        public ?Order $order,
    ) {}
}
