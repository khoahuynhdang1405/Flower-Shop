<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

/**
 * Một quà kèm trong giỏ. Quà miễn phí (thiệp viết tay) luôn tính 0 đ.
 */
final readonly class CartAddonLineData
{
    public function __construct(
        public int $addonId,
        public string $name,
        public int $unitPrice,
        public bool $isFree,
        public int $quantity,
        public ?string $imageUrl,
    ) {}

    public function total(): int
    {
        return $this->isFree ? 0 : $this->unitPrice * $this->quantity;
    }
}
