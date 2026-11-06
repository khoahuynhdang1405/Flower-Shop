<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Enums\DiscountType;
use App\Models\Promotion;

/**
 * Số tiền mã ưu đãi trừ vào tạm tính: giảm cố định, hoặc theo % (làm tròn xuống) có trần max_discount_amount.
 * Không bao giờ giảm quá tạm tính. Việc kiểm tra giá trị tối thiểu do nơi gọi đảm nhận.
 */
final class CalculatePromotionDiscountAction
{
    public function execute(Promotion $promotion, int $subtotal): int
    {
        $discount = match ($promotion->discount_type) {
            DiscountType::Fixed => $promotion->discount_value,
            DiscountType::Percentage => intdiv($subtotal * $promotion->discount_value, 100),
        };

        if ($promotion->max_discount_amount !== null) {
            $discount = min($discount, $promotion->max_discount_amount);
        }

        return min($discount, $subtotal);
    }
}
