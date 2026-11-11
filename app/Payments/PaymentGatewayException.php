<?php

declare(strict_types=1);

namespace App\Payments;

use RuntimeException;

/**
 * Cổng thanh toán không tạo được phiên thanh toán (lỗi mạng, cổng từ chối yêu cầu...).
 */
final class PaymentGatewayException extends RuntimeException {}
