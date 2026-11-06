<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\Models\Addon;
use App\Repositories\Contracts\AddonRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

/**
 * Tạm tính giỏ hàng (VNĐ), giá đọc lại từ DB:
 * hoa = số lượng × giá size (sản phẩm chưa có size thì base_price); quà kèm = số lượng × giá (miễn phí = 0).
 * Món đã ngừng bán hoặc size không còn thì không tính.
 */
final class CalculateCartSubtotalAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private AddonRepositoryInterface $addons,
        private CartRepositoryInterface $cart,
    ) {}

    public function execute(): int
    {
        return $this->flowersSubtotal() + $this->addonsSubtotal();
    }

    private function flowersSubtotal(): int
    {
        $lines = $this->cart->flowers();
        $products = $this->products->findForSaleWithSizes(array_column($lines, 'product_id'))->keyBy('id');

        return array_sum(array_map(function (array $line) use ($products): int {
            $product = $products->get($line['product_id']);
            $unitPrice = $line['size_id'] === null
                ? $product?->base_price
                : $product?->sizes->firstWhere('id', $line['size_id'])?->price;

            return ($unitPrice ?? 0) * $line['quantity'];
        }, $lines));
    }

    private function addonsSubtotal(): int
    {
        $quantities = $this->cart->addons();

        return $this->addons->findActiveByIds(array_keys($quantities))
            ->sum(fn (Addon $addon): int => $addon->is_free ? 0 : $addon->price * $quantities[$addon->id]);
    }
}
