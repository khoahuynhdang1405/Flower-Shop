<?php

declare(strict_types=1);

namespace App\DTOs\Order;

use App\Enums\PaymentMethod;

/**
 * Thông tin khách nhập ở trang thanh toán. Món, giá, ưu đãi không nằm ở đây mà đọc lại từ giỏ.
 * isGift = false: người nhận chính là người đặt, không kèm thiệp.
 */
final readonly class PlaceOrderData
{
    public function __construct(
        public ?int $userId,
        public string $senderName,
        public string $senderPhone,
        public string $senderEmail,
        public bool $isGift,
        public string $receiverName,
        public string $receiverPhone,
        public string $shippingAddress,
        public string $shippingDistrict,
        public string $deliveryDate,
        public string $timeSlot,
        public ?string $cardSender,
        public ?string $cardReceiver,
        public ?string $cardMessage,
        public ?string $deliveryNotes,
        public PaymentMethod $paymentMethod,
    ) {}
}
