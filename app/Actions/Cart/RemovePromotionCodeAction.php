<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Repositories\Contracts\CartRepositoryInterface;

/**
 * Khách bỏ mã ưu đãi đã nhập ở giỏ.
 */
final class RemovePromotionCodeAction
{
    public function __construct(private CartRepositoryInterface $cart) {}

    public function execute(): void
    {
        $this->cart->forgetPromotionCode();
    }
}
