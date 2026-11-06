<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Actions\Cart\RemoveAddonFromCartAction;
use App\Actions\Cart\UpdateCartAddonQuantityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\UpdateCartQuantityRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Quà kèm trong giỏ: đổi số lượng, xóa (kể cả thiệp miễn phí).
 */
class CartAddonController extends Controller
{
    public function update(UpdateCartQuantityRequest $request, int $addon, UpdateCartAddonQuantityAction $updateQuantity): RedirectResponse
    {
        $updateQuantity->execute($addon, $request->integer('quantity'));

        return to_route('cart.show');
    }

    public function destroy(int $addon, RemoveAddonFromCartAction $removeAddon): RedirectResponse
    {
        $removeAddon->execute($addon);

        return to_route('cart.show')->with('status', __('cart.flash.removed'));
    }
}
