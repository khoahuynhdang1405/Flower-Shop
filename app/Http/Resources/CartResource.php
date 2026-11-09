<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Cart\CartSummaryData;
use App\Enums\PromotionState;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Giỏ hàng (API): các món, tạm tính, mã ưu đãi, phí giao, tổng. Tiền tính bằng VNĐ (số nguyên).
 * shipping_fee null = chưa đủ ngưỡng miễn phí, phí giao tính ở bước thanh toán theo quận.
 *
 * @property CartSummaryData $resource
 */
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $summary = $this->resource;
        $lines = $summary->lines;

        return [
            'items' => CartItemResource::collection([...$lines->flowers, ...$lines->addons]),
            'item_count' => $lines->itemCount(),
            'subtotal' => $summary->subtotal,
            'promotion' => $summary->promotionCode === null ? null : [
                'code' => $summary->promotionCode,
                'state' => $summary->promotionState->value,
                'discount' => $summary->discount,
                'min_order_amount' => $summary->promotionMinOrder,
                'missing_amount' => $summary->promotionState === PromotionState::BelowMinimum
                    ? $summary->promotionMinOrder - $summary->subtotal
                    : 0,
            ],
            'free_shipping_from' => $summary->freeShippingFrom,
            'shipping_fee' => $summary->shippingFee,
            'total' => $summary->total,
        ];
    }
}
