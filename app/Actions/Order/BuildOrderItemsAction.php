<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\DTOs\Cart\CartAddonLineData;
use App\DTOs\Cart\CartFlowerLineData;
use App\DTOs\Cart\CartLinesData;
use App\Enums\CartItemType;

/**
 * Chuyển các dòng giỏ thành dòng order_items (snapshot tên, size, đơn giá lúc đặt).
 * Quà miễn phí (thiệp viết tay) vẫn ghi lại với giá 0 đ để tiệm biết cần chuẩn bị.
 */
final class BuildOrderItemsAction
{
    /**
     * @return list<array{item_type: CartItemType, item_id: int, item_name: string, size_name: ?string, unit_price: int, quantity: int, total_price: int}>
     */
    public function execute(CartLinesData $lines): array
    {
        return [
            ...array_map(fn (CartFlowerLineData $flower): array => [
                'item_type' => CartItemType::Flower,
                'item_id' => $flower->productId,
                'item_name' => $flower->name,
                'size_name' => $flower->sizeName,
                'unit_price' => $flower->unitPrice,
                'quantity' => $flower->quantity,
                'total_price' => $flower->total(),
            ], $lines->flowers),
            ...array_map(fn (CartAddonLineData $addon): array => [
                'item_type' => CartItemType::Addon,
                'item_id' => $addon->addonId,
                'item_name' => $addon->name,
                'size_name' => null,
                'unit_price' => $addon->isFree ? 0 : $addon->unitPrice,
                'quantity' => $addon->quantity,
                'total_price' => $addon->total(),
            ], $lines->addons),
        ];
    }
}
