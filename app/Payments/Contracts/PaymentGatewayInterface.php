<?php

declare(strict_types=1);

namespace App\Payments\Contracts;

use App\DTOs\Payment\PaymentRequestData;
use App\DTOs\Payment\PaymentResultData;
use App\Enums\PaymentConfirmationOutcome;
use Symfony\Component\HttpFoundation\Response;

/**
 * Một cổng thanh toán trực tuyến. Thêm cổng mới = thêm class implement interface này
 * và đăng ký trong PaymentServiceProvider.
 */
interface PaymentGatewayInterface
{
    /**
     * URL trang thanh toán của cổng để chuyển khách sang.
     */
    public function createPaymentUrl(PaymentRequestData $request): string;

    /**
     * Kiểm tra chữ ký và đọc kết quả từ tham số cổng gửi về (trang quay về và IPN dùng chung định dạng).
     *
     * @param  array<string, mixed>  $params
     */
    public function verify(array $params): PaymentResultData;

    /**
     * Phản hồi IPN theo đúng định dạng cổng yêu cầu.
     */
    public function ipnResponse(PaymentConfirmationOutcome $outcome): Response;
}
