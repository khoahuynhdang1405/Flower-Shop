<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

/**
 * Một dòng hoa trong giỏ, giá đọc lại từ DB: sizeId/sizeName null = sản phẩm chưa có size (tính base_price).
 * imageUrl null = chưa upload ảnh chính (giao diện dùng ảnh tĩnh theo slug hoặc khung giữ chỗ).
 */
final readonly class CartFlowerLineData
{
    public function __construct(
        public int $productId,
        public ?int $sizeId,
        public string $name,
        public string $slug,
        public ?string $sizeName,
        public int $unitPrice,
        public int $quantity,
        public ?string $imageUrl,
    ) {}

    public function total(): int
    {
        return $this->unitPrice * $this->quantity;
    }
}
