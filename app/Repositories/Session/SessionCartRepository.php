<?php

declare(strict_types=1);

namespace App\Repositories\Session;

use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Contracts\Session\Session;

/**
 * Giỏ hàng lưu trong session:
 * cart.flowers = ["{product_id}:{size_id|0}" => số lượng], cart.addons = [addon_id => số lượng],
 * cart.promotion_code = mã ưu đãi đã nhập.
 */
final class SessionCartRepository implements CartRepositoryInterface
{
    private const FLOWERS = 'cart.flowers';

    private const ADDONS = 'cart.addons';

    private const PROMOTION_CODE = 'cart.promotion_code';

    public function __construct(private Session $session) {}

    public function flowers(): array
    {
        $lines = [];

        foreach ($this->session->get(self::FLOWERS, []) as $key => $quantity) {
            [$productId, $sizeId] = array_map('intval', explode(':', (string) $key));
            $lines[] = ['product_id' => $productId, 'size_id' => $sizeId ?: null, 'quantity' => (int) $quantity];
        }

        return $lines;
    }

    public function flowerQuantity(int $productId, ?int $sizeId): int
    {
        return (int) $this->session->get(self::FLOWERS.'.'.$this->flowerKey($productId, $sizeId), 0);
    }

    public function putFlower(int $productId, ?int $sizeId, int $quantity): void
    {
        $this->session->put(self::FLOWERS.'.'.$this->flowerKey($productId, $sizeId), $quantity);
    }

    public function forgetFlower(int $productId, ?int $sizeId): void
    {
        $this->session->forget(self::FLOWERS.'.'.$this->flowerKey($productId, $sizeId));
    }

    public function addons(): array
    {
        return array_map('intval', $this->session->get(self::ADDONS, []));
    }

    public function addonQuantity(int $addonId): int
    {
        return (int) $this->session->get(self::ADDONS.'.'.$addonId, 0);
    }

    public function putAddon(int $addonId, int $quantity): void
    {
        $this->session->put(self::ADDONS.'.'.$addonId, $quantity);
    }

    public function forgetAddon(int $addonId): void
    {
        $this->session->forget(self::ADDONS.'.'.$addonId);
    }

    public function promotionCode(): ?string
    {
        return $this->session->get(self::PROMOTION_CODE);
    }

    public function putPromotionCode(string $code): void
    {
        $this->session->put(self::PROMOTION_CODE, $code);
    }

    public function forgetPromotionCode(): void
    {
        $this->session->forget(self::PROMOTION_CODE);
    }

    public function clear(): void
    {
        $this->session->forget('cart');
    }

    private function flowerKey(int $productId, ?int $sizeId): string
    {
        return $productId.':'.($sizeId ?? 0);
    }
}
