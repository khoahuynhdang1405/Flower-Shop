<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

/**
 * Các dòng còn mua được trong giỏ (món đã ngừng bán / size không còn đã bị bỏ ra).
 */
final readonly class CartLinesData
{
    /**
     * @param  list<CartFlowerLineData>  $flowers
     * @param  list<CartAddonLineData>  $addons
     */
    public function __construct(
        public array $flowers,
        public array $addons,
    ) {}

    public function isEmpty(): bool
    {
        return $this->flowers === [] && $this->addons === [];
    }

    public function subtotal(): int
    {
        return array_sum(array_map(fn (CartFlowerLineData|CartAddonLineData $line): int => $line->total(), [...$this->flowers, ...$this->addons]));
    }

    /**
     * Số sản phẩm khách mua: tổng số lượng hoa + quà tính tiền (thiệp miễn phí không tính).
     */
    public function itemCount(): int
    {
        return array_sum(array_map(fn (CartFlowerLineData|CartAddonLineData $line): int => $line->quantity, [...$this->flowers, ...$this->paidAddons()]));
    }

    /**
     * @return list<CartAddonLineData>
     */
    public function paidAddons(): array
    {
        return array_values(array_filter($this->addons, fn (CartAddonLineData $addon): bool => ! $addon->isFree));
    }

    /**
     * @return list<CartAddonLineData>
     */
    public function freeAddons(): array
    {
        return array_values(array_filter($this->addons, fn (CartAddonLineData $addon): bool => $addon->isFree));
    }
}
