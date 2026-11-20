<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Bốn mùa hoa của tiệm, mỗi mùa 3 tháng: Xuân 2–4, Hạ 5–7, Thu 8–10, Đông 11–1 (sang tháng 1 năm sau).
 * Giá trị trùng khóa trong lang/{locale}/seasons.php và tên ảnh mua-{value}.
 */
enum Season: string
{
    case Spring = 'spring';
    case Summer = 'summer';
    case Autumn = 'autumn';
    case Winter = 'winter';

    /**
     * Giá "từ" của bộ sưu tập mùa (VNĐ) trên banner mùa và trang Hoa theo mùa.
     * TODO: dữ liệu mẫu theo docs/designs/storefront/03-hoa-theo-mua.png; khi nối DB lấy giá size thấp nhất trong bộ sưu tập mùa.
     */
    public function startingPrice(): int
    {
        return match ($this) {
            self::Spring => 620000,
            self::Summer => 550000,
            self::Autumn => 590000,
            self::Winter => 650000,
        };
    }

    /**
     * Slug bộ sưu tập Theo mùa (categories.type = season) của mùa này; ảnh chính của bộ sưu tập
     * hiện ở banner mùa và thẻ mùa. Tên bộ sưu tập khớp lang seasons.hero.{mùa}.collection.
     */
    public function collectionSlug(): string
    {
        return match ($this) {
            self::Spring => 'trong-treo-mua-xuan',
            self::Summer => 'ruc-ro-mua-ha',
            self::Autumn => 'diu-dang-mua-thu',
            self::Winter => 'an-lanh-mua-dong',
        };
    }

    public static function fromMonth(int $month): self
    {
        // Dời mốc về tháng 2 (đầu xuân) để tháng 1 rơi vào cuối mùa đông.
        return match (intdiv(($month + 10) % 12, 3)) {
            0 => self::Spring,
            1 => self::Summer,
            2 => self::Autumn,
            default => self::Winter,
        };
    }
}
