<?php

declare(strict_types=1);

namespace App\Actions\Cart;

use App\DTOs\Cart\CartAddonLineData;
use App\DTOs\Cart\CartFlowerLineData;
use App\DTOs\Cart\CartLinesData;
use App\Models\Addon;
use App\Repositories\Contracts\AddonRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

/**
 * Dựng các dòng giỏ hàng từ session + giá trong DB:
 * hoa = giá size (sản phẩm chưa có size thì base_price); quà kèm = giá món (miễn phí = 0).
 * Món đã ngừng bán hoặc size không còn thì bỏ ra (không hiện, không tính tiền).
 */
final class ListCartLinesAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private AddonRepositoryInterface $addons,
        private CartRepositoryInterface $cart,
    ) {}

    public function execute(): CartLinesData
    {
        return new CartLinesData($this->flowerLines(), $this->addonLines());
    }

    /**
     * @return list<CartFlowerLineData>
     */
    private function flowerLines(): array
    {
        $lines = $this->cart->flowers();

        if ($lines === []) {
            return [];
        }

        $products = $this->products->findForSaleWithSizes(array_values(array_unique(array_column($lines, 'product_id'))))->keyBy('id');
        $result = [];

        foreach ($lines as $line) {
            $product = $products->get($line['product_id']);
            $size = $line['size_id'] === null ? null : $product?->sizes->firstWhere('id', $line['size_id']);

            if ($product === null || ($line['size_id'] !== null && $size === null)) {
                continue;
            }

            $result[] = new CartFlowerLineData(
                productId: $product->id,
                sizeId: $size?->id,
                name: $product->name,
                slug: $product->slug,
                sizeName: $size?->size_name,
                unitPrice: $size?->price ?? $product->base_price,
                quantity: $line['quantity'],
                imageUrl: $product->primaryImage?->url,
            );
        }

        return $result;
    }

    /**
     * @return list<CartAddonLineData>
     */
    private function addonLines(): array
    {
        $quantities = $this->cart->addons();

        if ($quantities === []) {
            return [];
        }

        return $this->addons->findActiveByIds(array_keys($quantities))
            ->map(fn (Addon $addon): CartAddonLineData => new CartAddonLineData(
                addonId: $addon->id,
                name: $addon->name,
                unitPrice: $addon->price,
                isFree: $addon->is_free,
                quantity: $quantities[$addon->id],
                imageUrl: $addon->image_url,
            ))
            ->values()
            ->all();
    }
}
