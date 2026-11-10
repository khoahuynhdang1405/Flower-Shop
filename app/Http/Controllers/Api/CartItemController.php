<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Cart\AddProductSelectionToCartAction;
use App\Actions\Cart\GetCartSummaryAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartItemQuantityAction;
use App\DTOs\Cart\CartItemKeyData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\AddToCartRequest;
use App\Http\Requests\Storefront\UpdateCartQuantityRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\JsonResponse;

/**
 * /api/cart/items: thêm hoa (kèm quà), đổi số lượng, xóa món. {item} dạng flower-3-7 / flower-3 / addon-1.
 * Mỗi lần đổi đều trả lại cả giỏ sau khi đổi.
 */
class CartItemController extends Controller
{
    public function store(AddToCartRequest $request, AddProductSelectionToCartAction $addSelection, GetCartSummaryAction $getSummary): JsonResponse
    {
        $addSelection->execute($request->toFlowerData(), $request->addonIds());

        return (new CartResource($getSummary->execute()))->response()->setStatusCode(201);
    }

    public function update(UpdateCartQuantityRequest $request, string $item, UpdateCartItemQuantityAction $updateQuantity, GetCartSummaryAction $getSummary): CartResource
    {
        $updateQuantity->execute(CartItemKeyData::fromString($item), $request->integer('quantity'));

        return new CartResource($getSummary->execute());
    }

    public function destroy(string $item, RemoveCartItemAction $removeItem, GetCartSummaryAction $getSummary): CartResource
    {
        $removeItem->execute(CartItemKeyData::fromString($item));

        return new CartResource($getSummary->execute());
    }
}
