<?php

declare(strict_types=1);

namespace App\DTOs\Payment;

/**
 * Yêu cầu tạo phiên thanh toán gửi sang cổng.
 * reference là mã giao dịch phía tiệm, mỗi lần thanh toán (kể cả thanh toán lại) một mã khác nhau.
 */
final readonly class PaymentRequestData
{
    public function __construct(
        public string $reference,
        public int $amount,
        public string $description,
        public string $returnUrl,
        public string $ipnUrl,
        public string $clientIp,
    ) {}
}
