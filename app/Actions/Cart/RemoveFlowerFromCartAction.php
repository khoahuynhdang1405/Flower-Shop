<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Repositories\Contracts\CartRepositoryInterface;

/**
 * "Xóa" một dòng hoa (sản phẩm + size) khỏi giỏ. Dòng không còn trong giỏ thì bỏ qua (bấm hai lần không lỗi).
 */
final class RemoveFlowerFromCartAction
{
    public function __construct(private CartRepositoryInterface $cart) {}

    public function execute(int $productId, ?int $sizeId): void
    {
        $this->cart->forgetFlower($productId, $sizeId);
    }
}
