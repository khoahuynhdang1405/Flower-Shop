<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\Cart\CartItemKeyData;
use App\Enums\CartItemType;
use Illuminate\Validation\ValidationException;

/**
 * API giỏ hàng: đổi số lượng một món theo id (hoa hoặc quà kèm).
 */
final class UpdateCartItemQuantityAction
{
    public function __construct(
        private UpdateCartFlowerQuantityAction $updateFlower,
        private UpdateCartAddonQuantityAction $updateAddon,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(CartItemKeyData $item, int $quantity): void
    {
        match ($item->type) {
            CartItemType::Flower => $this->updateFlower->execute($item->id, $item->sizeId, $quantity),
            CartItemType::Addon => $this->updateAddon->execute($item->id, $quantity),
        };
    }
}
