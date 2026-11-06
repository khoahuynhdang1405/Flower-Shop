<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Actions\Cart\AddProductSelectionToCartAction;
use App\Actions\Cart\RemoveFlowerFromCartAction;
use App\Actions\Cart\UpdateCartFlowerQuantityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddToCartRequest;
use App\Http\Requests\Storefront\UpdateCartQuantityRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Dòng hoa trong giỏ: thêm (từ trang chi tiết, kèm quà đã tích), đổi số lượng, xóa.
 */
class CartFlowerController extends Controller
{
    public function store(AddToCartRequest $request, AddProductSelectionToCartAction $addSelection): RedirectResponse
    {
        $addSelection->execute($request->toFlowerData(), $request->addonIds());

        return to_route('cart.show')->with('status', __('cart.flash.added'));
    }

    public function update(UpdateCartQuantityRequest $request, int $product, UpdateCartFlowerQuantityAction $updateQuantity, ?int $size = null): RedirectResponse
    {
        $updateQuantity->execute($product, $size, $request->integer('quantity'));

        return to_route('cart.show');
    }

    public function destroy(int $product, RemoveFlowerFromCartAction $removeFlower, ?int $size = null): RedirectResponse
    {
        $removeFlower->execute($product, $size);

        return to_route('cart.show')->with('status', __('cart.flash.removed'));
    }
}
