<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

use App\Enums\PromotionState;

/**
 * Khối "Gói ghém đơn hoa" ở giỏ hàng.
 * shippingFee null = chưa đủ ngưỡng miễn phí, phí giao tính ở bước thanh toán (theo quận).
 * promotionState null = khách chưa nhập mã; promotionMinOrder là giá trị tối thiểu của mã (để nhắc khi chưa đủ).
 */
final readonly class CartSummaryData
{
    public function __construct(
        public CartLinesData $lines,
        public int $subtotal,
        public ?string $promotionCode,
        public ?PromotionState $promotionState,
        public int $promotionMinOrder,
        public int $discount,
        public int $freeShippingFrom,
        public ?int $shippingFee,
        public int $total,
    ) {}
}
