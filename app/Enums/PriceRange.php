<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Khoảng giá ở bộ lọc "Tất cả hoa" (giá trị = tham số ?price=).
 * Cận dưới tính vào khoảng, cận trên không: 800.000 đ thuộc "800.000 – 1.200.000", 1.200.000 đ thuộc "Trên 1.200.000".
 */
enum PriceRange: string
{
    case Under500 = 'under_500';
    case From500To800 = '500_800';
    case From800To1200 = '800_1200';
    case Over1200 = 'over_1200';

    public function min(): ?int
    {
        return match ($this) {
            self::Under500 => null,
            self::From500To800 => 500000,
            self::From800To1200 => 800000,
            self::Over1200 => 1200000,
        };
    }

    public function max(): ?int
    {
        return match ($this) {
            self::Under500 => 500000,
            self::From500To800 => 800000,
            self::From800To1200 => 1200000,
            self::Over1200 => null,
        };
    }
}
