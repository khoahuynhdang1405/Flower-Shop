<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\Cart\CartItemKeyData;
use App\Enums\CartItemType;

/**
 * API giỏ hàng: xóa một món theo id (hoa hoặc quà kèm).
 */
final class RemoveCartItemAction
{
    public function __construct(
        private RemoveFlowerFromCartAction $removeFlower,
        private RemoveAddonFromCartAction $removeAddon,
    ) {}

    public function execute(CartItemKeyData $item): void
    {
        match ($item->type) {
            CartItemType::Flower => $this->removeFlower->execute($item->id, $item->sizeId),
            CartItemType::Addon => $this->removeAddon->execute($item->id),
        };
    }
}
