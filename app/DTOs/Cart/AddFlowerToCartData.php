<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

/**
 * Khách bỏ một bó hoa vào giỏ: sản phẩm + size đã chọn (null khi sản phẩm chưa có size) + số lượng.
 */
final readonly class AddFlowerToCartData
{
    public function __construct(
        public int $productId,
        public ?int $sizeId,
        public int $quantity,
    ) {}
}
