<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Repositories\Contracts\AddonRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Validation\ValidationException;

/**
 * Bộ tăng/giảm ở giỏ hàng: gán số lượng mới cho một quà kèm.
 * Tối thiểu 1, quá 99 thì giữ ở 99; quà miễn phí (thiệp viết tay) cố định 1 phần — chỉ xóa được.
 */
final class UpdateCartAddonQuantityAction
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

        if ($this->cart->addonQuantity($addonId) === 0) {
            throw ValidationException::withMessages(['item' => __('cart.item_missing')]);
        }

        if ($quantity > 1 && $this->addons->findActiveByIds([$addonId])->first()?->is_free) {
            throw ValidationException::withMessages(['quantity' => __('cart.free_addon_single')]);
        }

        $this->cart->putAddon($addonId, min($quantity, AddFlowerToCartAction::MAX_QUANTITY));
    }
}
