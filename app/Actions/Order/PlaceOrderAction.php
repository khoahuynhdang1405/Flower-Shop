<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Checkout\GetCheckoutSummaryAction;
use App\DTOs\Order\PlaceOrderData;
use App\Enums\OrderStatus;
use App\Enums\PromotionState;
use App\Models\Order;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Đặt hoa: tính lại tiền từ giỏ (không tin số liệu trình duyệt gửi), lưu đơn + lịch giao + các món
 * + mốc "đã tiếp nhận" trong một transaction, rồi làm trống giỏ. Đơn COD gửi email xác nhận ngay.
 */
final class PlaceOrderAction
{
    public function __construct(
        private GetCheckoutSummaryAction $getSummary,
        private BuildOrderItemsAction $buildItems,
        private GenerateOrderCodeAction $generateCode,
        private OrderRepositoryInterface $orders,
        private PromotionRepositoryInterface $promotions,
        private CartRepositoryInterface $cart,
        private SendOrderConfirmationAction $sendConfirmation,
    ) {}

    public function execute(PlaceOrderData $data): Order
    {
        $summary = $this->getSummary->execute();

        if ($summary->lines->isEmpty()) {
            throw ValidationException::withMessages(['cart' => __('order.errors.empty_cart')]);
        }

        $promotion = $summary->promotionState === PromotionState::Applied
            ? $this->promotions->findActiveByCode((string) $summary->promotionCode)
            : null;

        $order = DB::transaction(function () use ($data, $summary, $promotion): Order {
            // order_code bắt buộc và duy nhất nhưng số thứ tự lấy từ id: ghi mã tạm, có id rồi đổi sang mã thật.
            $order = $this->orders->create([
                'user_id' => $data->userId,
                'order_code' => (string) Str::uuid(),
                'subtotal' => $summary->subtotal,
                'discount_amount' => $summary->discount,
                'shipping_fee' => $summary->shippingFee,
                'total_amount' => $summary->total,
                'promotion_id' => $promotion?->id,
                'payment_method' => $data->paymentMethod,
            ]);
            $this->orders->assignOrderCode($order, $this->generateCode->execute($order->id));

            $this->orders->createDeliveryDetail($order, $this->deliveryAttributes($data));
            $this->orders->createItems($order, $this->buildItems->execute($summary->lines));
            $this->orders->createStatusLog($order, [
                'status' => OrderStatus::Received,
                'title' => __('order.status_logs.received', locale: 'vi'),
                'description' => __('order.status_logs.received_description', locale: 'vi'),
            ]);

            return $order;
        });

        $this->cart->clear();

        // Đơn VNPAY/MoMo gửi email khi đã thanh toán (ConfirmOrderPaymentAction).
        if (! $order->payment_method->isOnline()) {
            $this->sendConfirmation->execute($order);
        }

        return $order;
    }

    /**
     * Khách tự nhận hoa: người nhận là người đặt, bỏ phần thiệp.
     *
     * @return array<string, mixed>
     */
    private function deliveryAttributes(PlaceOrderData $data): array
    {
        return [
            'sender_name' => $data->senderName,
            'sender_phone' => $data->senderPhone,
            'sender_email' => $data->senderEmail,
            'is_gift' => $data->isGift,
            'receiver_name' => $data->isGift ? $data->receiverName : $data->senderName,
            'receiver_phone' => $data->isGift ? $data->receiverPhone : $data->senderPhone,
            'shipping_address' => $data->shippingAddress,
            'shipping_district' => $data->shippingDistrict,
            'delivery_date' => $data->deliveryDate,
            'time_slot' => $data->timeSlot,
            'card_sender' => $data->isGift ? $data->cardSender : null,
            'card_receiver' => $data->isGift ? $data->cardReceiver : null,
            'card_message' => $data->isGift ? $data->cardMessage : null,
            'delivery_notes' => $data->deliveryNotes,
        ];
    }
}
