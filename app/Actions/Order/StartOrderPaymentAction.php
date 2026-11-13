<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\DTOs\Payment\PaymentRequestData;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Payments\PaymentGatewayResolver;
use Illuminate\Support\Str;
use LogicException;

/**
 * Tạo phiên thanh toán cho đơn VNPAY/MoMo chưa trả tiền, trả về URL trang thanh toán của cổng.
 * Mã giao dịch "LH{id đơn}T{unix time}": mỗi lần thanh toán lại là một mã mới, vẫn tách ngược ra được id đơn.
 */
final class StartOrderPaymentAction
{
    public function __construct(private PaymentGatewayResolver $gateways) {}

    public function execute(Order $order, string $clientIp): string
    {
        if (! $order->payment_method->isOnline() || $order->payment_status === PaymentStatus::Paid) {
            throw new LogicException("Order [{$order->id}] does not need an online payment.");
        }

        return $this->gateways->for($order->payment_method)->createPaymentUrl(new PaymentRequestData(
            reference: 'LH'.$order->id.'T'.now()->timestamp,
            amount: $order->total_amount,
            // Cổng chỉ nhận chữ không dấu, không ký tự đặc biệt.
            description: Str::ascii('Thanh toan don hoa '.ltrim($order->order_code, '#')),
            returnUrl: route('payment.return', $order->payment_method),
            ipnUrl: route('payment.ipn', $order->payment_method),
            clientIp: $clientIp,
        ));
    }
}
