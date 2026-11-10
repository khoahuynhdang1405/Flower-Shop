<?php

declare(strict_types=1);

namespace App\Actions\Checkout;

use App\Actions\Cart\GetCartSummaryAction;
use App\DTOs\Cart\CartSummaryData;
use Illuminate\Container\Attributes\Config;

/**
 * Khối "Đơn hoa của bạn" ở trang thanh toán: như giỏ hàng nhưng phí giao đã chốt.
 * Tạm tính (trước ưu đãi) từ ngưỡng miễn phí thì 0 đ, chưa đủ thì phí giao nội thành cố định.
 */
final class GetCheckoutSummaryAction
{
    public function __construct(
        private GetCartSummaryAction $getCartSummary,
        #[Config('shop.shipping_fee')]
        private int $shippingFee,
    ) {}

    public function execute(): CartSummaryData
    {
        $cart = $this->getCartSummary->execute();
        $shippingFee = $cart->shippingFee ?? $this->shippingFee;

        return new CartSummaryData(
            lines: $cart->lines,
            subtotal: $cart->subtotal,
            promotionCode: $cart->promotionCode,
            promotionState: $cart->promotionState,
            promotionMinOrder: $cart->promotionMinOrder,
            discount: $cart->discount,
            freeShippingFrom: $cart->freeShippingFrom,
            shippingFee: $shippingFee,
            total: $cart->subtotal - $cart->discount + $shippingFee,
        );
    }
}
