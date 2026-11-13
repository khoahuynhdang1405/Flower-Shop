<?php

declare(strict_types=1);

namespace App\DTOs\Payment;

/**
 * Dữ liệu cổng gửi về (trang quay về hoặc IPN) sau khi đã kiểm tra chữ ký.
 * Chữ ký sai thì các trường khác không đáng tin, chỉ dùng isSignatureValid.
 */
final readonly class PaymentResultData
{
    public function __construct(
        public bool $isSignatureValid,
        public bool $isSuccessful,
        public string $reference,
        public int $amount,
        public ?string $transactionRef,
    ) {}

    public static function invalidSignature(): self
    {
        return new self(isSignatureValid: false, isSuccessful: false, reference: '', amount: 0, transactionRef: null);
    }
}
