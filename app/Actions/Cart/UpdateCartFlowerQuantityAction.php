<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Validation\ValidationException;

/**
 * Bộ tăng/giảm ở giỏ hàng: gán số lượng mới cho một dòng hoa (sản phẩm + size).
 * Tối thiểu 1 (bỏ món thì dùng "Xóa"), quá 99 thì giữ ở 99.
 */
final class UpdateCartFlowerQuantityAction
{
    public function __construct(private CartRepositoryInterface $cart) {}

    /**
     * @throws ValidationException
     */
    public function execute(int $productId, ?int $sizeId, int $quantity): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => __('cart.quantity_invalid')]);
        }

        if ($this->cart->flowerQuantity($productId, $sizeId) === 0) {
            throw ValidationException::withMessages(['item' => __('cart.item_missing')]);
        }

        $this->cart->putFlower($productId, $sizeId, min($quantity, AddFlowerToCartAction::MAX_QUANTITY));
    }
}
