<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\DTOs\Payment\PaymentConfirmationData;
use App\Enums\PaymentConfirmationOutcome;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Payments\PaymentGatewayResolver;
use App\Repositories\Contracts\OrderRepositoryInterface;

/**
 * Ghi kết quả thanh toán cổng gửi về (trang quay về và IPN dùng chung, gọi lặp lại vẫn an toàn).
 * Chỉ tin dữ liệu có chữ ký đúng và số tiền khớp tổng đơn; đơn đã thanh toán thì không đổi nữa.
 */
final class ConfirmOrderPaymentAction
{
    public function __construct(
        private PaymentGatewayResolver $gateways,
        private OrderRepositoryInterface $orders,
        private SendOrderConfirmationAction $sendConfirmation,
    ) {}

    /**
     * @param  array<string, mixed>  $params
     */
    public function execute(PaymentMethod $method, array $params): PaymentConfirmationData
    {
        $result = $this->gateways->for($method)->verify($params);

        if (! $result->isSignatureValid) {
            return new PaymentConfirmationData(PaymentConfirmationOutcome::InvalidSignature, null);
        }

        $order = preg_match('/^LH(\d+)T\d+$/', $result->reference, $matches) === 1
            ? $this->orders->findById((int) $matches[1])
            : null;

        if ($order === null || $order->payment_method !== $method) {
            return new PaymentConfirmationData(PaymentConfirmationOutcome::OrderNotFound, null);
        }

        if ($result->amount !== $order->total_amount) {
            return new PaymentConfirmationData(PaymentConfirmationOutcome::InvalidAmount, $order);
        }

        if ($order->payment_status === PaymentStatus::Paid) {
            return new PaymentConfirmationData(PaymentConfirmationOutcome::AlreadyConfirmed, $order);
        }

        if ($result->isSuccessful) {
            // Trang quay về và IPN có thể tới cùng lúc: chỉ lần thật sự đổi sang "đã thanh toán" mới gửi email.
            if ($this->orders->markPaymentPaid($order, $result->transactionRef)) {
                $this->sendConfirmation->execute($order);
            }
        } elseif ($order->payment_status === PaymentStatus::Pending) {
            $this->orders->markPaymentFailed($order);
        }

        return new PaymentConfirmationData(PaymentConfirmationOutcome::Confirmed, $order);
    }
}
