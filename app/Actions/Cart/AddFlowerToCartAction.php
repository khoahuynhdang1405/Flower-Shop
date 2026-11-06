<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\Cart\AddFlowerToCartData;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Validation\ValidationException;

/**
 * Thêm bó hoa vào giỏ. Cùng sản phẩm + cùng size thì cộng dồn vào dòng cũ; khác size là dòng mới.
 * Sản phẩm phải đang bán; có size thì phải chọn đúng size của sản phẩm đó, chưa có size thì không gửi size.
 */
final class AddFlowerToCartAction
{
    /** Trùng giới hạn ô số lượng ở trang chi tiết. */
    public const MAX_QUANTITY = 99;

    public function __construct(
        private ProductRepositoryInterface $products,
        private CartRepositoryInterface $cart,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(AddFlowerToCartData $data): void
    {
        if ($data->quantity < 1) {
            throw ValidationException::withMessages(['quantity' => __('cart.quantity_invalid')]);
        }

        $product = $this->products->findForSaleWithSizes([$data->productId])->first()
            ?? throw ValidationException::withMessages(['product' => __('cart.product_unavailable')]);

        $isValidSize = $product->sizes->isEmpty()
            ? $data->sizeId === null
            : $product->sizes->contains('id', $data->sizeId);

        if (! $isValidSize) {
            throw ValidationException::withMessages(['size' => __('cart.size_unavailable')]);
        }

        $quantity = $this->cart->flowerQuantity($product->id, $data->sizeId) + $data->quantity;

        $this->cart->putFlower($product->id, $data->sizeId, min($quantity, self::MAX_QUANTITY));
    }
}
