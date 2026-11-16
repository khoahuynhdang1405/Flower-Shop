<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Order\PlaceOrderAction;
use App\Actions\Order\ShowOrderConfirmationAction;
use App\Actions\Order\StartOrderPaymentAction;
use App\Actions\Order\TrackOrderAction;
use App\Enums\PaymentStatus;
use App\Http\Requests\Storefront\PlacedOrderRequest;
use App\Http\Requests\Storefront\PlaceOrderRequest;
use App\Http\Requests\Storefront\TrackOrderRequest;
use App\Models\Order;
use App\Payments\PaymentGatewayException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Đặt hoa (bước 2/3 → 3/3), trang xác nhận đơn, thanh toán lại qua VNPAY/MoMo và tra cứu đơn.
 */
class OrderController extends Controller
{
    public function store(PlaceOrderRequest $request, PlaceOrderAction $placeOrder, StartOrderPaymentAction $startPayment): Response
    {
        $order = $placeOrder->execute($request->toData());

        $request->session()->push(PlacedOrderRequest::SESSION_KEY, $order->id);

        if (! $order->payment_method->isOnline()) {
            return to_route('orders.confirmation', $order);
        }

        return $this->redirectToGateway($order, $request, $startPayment);
    }

    public function show(PlacedOrderRequest $request, ShowOrderConfirmationAction $showConfirmation): View
    {
        return view('orders.confirmation', [
            'order' => $showConfirmation->execute($request->orderId()),
        ]);
    }

    public function pay(PlacedOrderRequest $request, ShowOrderConfirmationAction $showConfirmation, StartOrderPaymentAction $startPayment): Response
    {
        $order = $showConfirmation->execute($request->orderId());

        abort_unless($order->payment_method->isOnline() && $order->payment_status !== PaymentStatus::Paid, 404);

        return $this->redirectToGateway($order, $request, $startPayment);
    }

    /**
     * Tra cứu & theo dõi đơn: chỉ form khi chưa nhập, có mã + SĐT thì hiện hành trình đơn (hoặc báo không tìm thấy).
     */
    public function track(TrackOrderRequest $request, TrackOrderAction $trackOrder): View
    {
        if (! $request->hasLookup()) {
            return view('orders.track', ['order' => null, 'searched' => false]);
        }

        return view('orders.track', [
            'order' => $trackOrder->execute((int) $request->user()->id, $request->orderCode(), $request->phone()),
            'searched' => true,
        ]);
    }

    /**
     * Cổng lỗi thì đơn vẫn giữ, khách về trang xác nhận để thanh toán lại.
     */
    private function redirectToGateway(Order $order, Request $request, StartOrderPaymentAction $startPayment): Response
    {
        try {
            return redirect()->away($startPayment->execute($order, (string) $request->ip()));
        } catch (PaymentGatewayException $exception) {
            Log::warning('Payment gateway failed', ['order_id' => $order->id, 'error' => $exception->getMessage()]);

            return to_route('orders.confirmation', $order)->with('payment_error', true);
        }
    }
}
