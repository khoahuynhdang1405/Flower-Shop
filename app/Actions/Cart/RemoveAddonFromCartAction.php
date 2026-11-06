<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Repositories\Contracts\CartRepositoryInterface;

/**
 * "Xóa" một quà kèm khỏi giỏ (kể cả thiệp miễn phí). Món không còn trong giỏ thì bỏ qua.
 */
final class RemoveAddonFromCartAction
{
    public function __construct(private CartRepositoryInterface $cart) {}

    public function execute(int $addonId): void
    {
        $this->cart->forgetAddon($addonId);
    }
}
