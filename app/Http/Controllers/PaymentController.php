<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Order\ConfirmOrderPaymentAction;
use App\Enums\PaymentMethod;
use App\Payments\PaymentGatewayResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cổng VNPAY/MoMo gửi kết quả về: trình duyệt khách quay lại (return) và cổng gọi ngầm (IPN).
 * Cả hai đều kiểm tra chữ ký rồi ghi kết quả; IPN là nguồn chính vì không phụ thuộc trình duyệt khách.
 */
class PaymentController extends Controller
{
    public function return(Request $request, PaymentMethod $method, ConfirmOrderPaymentAction $confirmPayment): RedirectResponse
    {
        $confirmation = $confirmPayment->execute($method, $request->query());

        if ($confirmation->order === null) {
            return to_route('home');
        }

        return to_route('orders.confirmation', $confirmation->order);
    }

    public function ipn(Request $request, PaymentMethod $method, ConfirmOrderPaymentAction $confirmPayment, PaymentGatewayResolver $gateways): Response
    {
        $confirmation = $confirmPayment->execute($method, $request->all());

        return $gateways->for($method)->ipnResponse($confirmation->outcome);
    }
}
