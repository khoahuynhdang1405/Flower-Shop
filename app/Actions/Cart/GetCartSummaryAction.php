<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\Cart\CartSummaryData;
use App\Enums\PromotionState;
use App\Models\Promotion;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Container\Attributes\Config;

/**
 * Trang giỏ hàng: các dòng + tạm tính, ưu đãi, phí giao dự kiến, tổng dự kiến.
 * Mã ưu đãi được kiểm tra lại mỗi lần (có thể đã bị tắt, hoặc giỏ chưa đủ giá trị tối thiểu).
 * Phí giao: tạm tính (trước ưu đãi) từ ngưỡng miễn phí thì 0 đ; chưa đủ thì tính ở bước thanh toán theo quận.
 */
final class GetCartSummaryAction
{
    public function __construct(
        private ListCartLinesAction $listLines,
        private CalculatePromotionDiscountAction $calculateDiscount,
        private CartRepositoryInterface $cart,
        private PromotionRepositoryInterface $promotions,
        #[Config('shop.free_shipping_from')]
        private int $freeShippingFrom,
    ) {}

    public function execute(): CartSummaryData
    {
        $lines = $this->listLines->execute();
        $subtotal = $lines->subtotal();
        $code = $this->cart->promotionCode();
        $promotion = $code === null ? null : $this->promotions->findActiveByCode($code);
        $state = $this->promotionState($code, $promotion, $subtotal);
        $discount = $state === PromotionState::Applied ? $this->calculateDiscount->execute($promotion, $subtotal) : 0;
        $shippingFee = $subtotal >= $this->freeShippingFrom ? 0 : null;

        return new CartSummaryData(
            lines: $lines,
            subtotal: $subtotal,
            promotionCode: $code,
            promotionState: $state,
            promotionMinOrder: $promotion->min_order_amount ?? 0,
            discount: $discount,
            freeShippingFrom: $this->freeShippingFrom,
            shippingFee: $shippingFee,
            total: $subtotal - $discount + ($shippingFee ?? 0),
        );
    }

    private function promotionState(?string $code, ?Promotion $promotion, int $subtotal): ?PromotionState
    {
        return match (true) {
            $code === null => null,
            $promotion === null => PromotionState::Unavailable,
            $subtotal < $promotion->min_order_amount => PromotionState::BelowMinimum,
            default => PromotionState::Applied,
        };
    }
}
