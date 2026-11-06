<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Repositories\Contracts\AddonRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Validation\ValidationException;

/**
 * Thêm quà kèm vào giỏ (dòng riêng, không gắn với bó hoa nào). Thêm lại cùng món thì cộng dồn số lượng;
 * quà miễn phí (thiệp viết tay) luôn chỉ 1 phần.
 */
final class AddAddonToCartAction
{
    public function __construct(
        private AddonRepositoryInterface $addons,
        private CartRepositoryInterface $cart,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(int $addonId, int $quantity): void
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => __('cart.quantity_invalid')]);
        }

        $addon = $this->addons->findActiveByIds([$addonId])->first()
            ?? throw ValidationException::withMessages(['addon' => __('cart.addon_unavailable')]);

        $total = $addon->is_free
            ? 1
            : min($this->cart->addonQuantity($addonId) + $quantity, AddFlowerToCartAction::MAX_QUANTITY);

        $this->cart->putAddon($addonId, $total);
    }
}
