<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

use App\Enums\CartItemType;
use InvalidArgumentException;

/**
 * Id của một món trong giỏ ở API: "flower-{product}-{size}" (không có size thì "flower-{product}") hoặc "addon-{addon}".
 * Giỏ lưu trong session không có id dòng riêng — dòng hoa xác định bằng cặp (sản phẩm, size), quà kèm bằng addon_id.
 */
final readonly class CartItemKeyData
{
    /** Regex cho tham số route {item}. */
    public const PATTERN = 'flower-[0-9]+(?:-[0-9]+)?|addon-[0-9]+';

    public function __construct(
        public CartItemType $type,
        public int $id,
        public ?int $sizeId = null,
    ) {}

    /**
     * @throws InvalidArgumentException
     */
    public static function fromString(string $key): self
    {
        if (! preg_match('/^(?:flower-(\d+)(?:-(\d+))?|addon-(\d+))$/', $key, $matches)) {
            throw new InvalidArgumentException("Invalid cart item key [{$key}].");
        }

        if (isset($matches[3])) {
            return new self(CartItemType::Addon, (int) $matches[3]);
        }

        return new self(CartItemType::Flower, (int) $matches[1], isset($matches[2]) ? (int) $matches[2] : null);
    }

    public function toString(): string
    {
        return implode('-', array_filter([$this->type->value, $this->id, $this->sizeId], fn (mixed $part): bool => $part !== null));
    }
}
