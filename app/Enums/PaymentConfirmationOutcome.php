<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Kết quả xử lý một thông báo thanh toán từ cổng (trang quay về hoặc IPN).
 */
enum PaymentConfirmationOutcome
{
    /** Chữ ký hợp lệ, đơn đã được cập nhật (đã thanh toán hoặc thất bại). */
    case Confirmed;

    /** Đơn đã có kết quả thanh toán từ trước, không ghi lại. */
    case AlreadyConfirmed;

    case OrderNotFound;

    case InvalidAmount;

    case InvalidSignature;
}
