<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Giỏ hàng của khách: chỉ lưu id + số lượng (không lưu giá — giá luôn đọc lại từ DB khi tính tiền).
 * Một dòng hoa = một cặp (sản phẩm, size); sizeId null = sản phẩm chưa có size.
 */
interface CartRepositoryInterface
{
    /**
     * @return list<array{product_id: int, size_id: ?int, quantity: int}>
     */
    public function flowers(): array;

    public function flowerQuantity(int $productId, ?int $sizeId): int;

    public function putFlower(int $productId, ?int $sizeId, int $quantity): void;

    public function forgetFlower(int $productId, ?int $sizeId): void;

    /**
     * @return array<int, int> addon_id => số lượng
     */
    public function addons(): array;

    public function addonQuantity(int $addonId): int;

    public function putAddon(int $addonId, int $quantity): void;

    public function forgetAddon(int $addonId): void;
}
