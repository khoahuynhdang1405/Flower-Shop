<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\Cart\AddFlowerToCartData;
use Illuminate\Validation\ValidationException;

/**
 * Nút "Thêm vào giỏ" ở trang chi tiết: bó hoa (size + số lượng) cùng các món quà kèm khách đã tích, mỗi món 1 phần.
 */
final class AddProductSelectionToCartAction
{
    public function __construct(
        private AddFlowerToCartAction $addFlower,
        private AddAddonToCartAction $addAddon,
    ) {}

    /**
     * @param  list<int>  $addonIds
     *
     * @throws ValidationException
     */
    public function execute(AddFlowerToCartData $flower, array $addonIds): void
    {
        $this->addFlower->execute($flower);

        foreach ($addonIds as $addonId) {
            $this->addAddon->execute($addonId, 1);
        }
    }
}
