<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Sắc hoa của sản phẩm (product_colors.color): bộ lọc "Sắc hoa bạn thích" và mục chọn ở Admin › Ảnh sản phẩm.
 * Mã màu lấy mẫu từ docs/designs/storefront/02-tat-ca-hoa.png.
 */
enum FlowerColor: string
{
    case Pink = 'pink';
    case Peach = 'peach';
    case Yellow = 'yellow';
    case White = 'white';
    case Red = 'red';
    case Green = 'green';

    public function label(): string
    {
        return __('common.flower_colors.'.$this->value);
    }

    public function hex(): string
    {
        return match ($this) {
            self::Pink => '#dfa3ae',
            self::Peach => '#f2d9cf',
            self::Yellow => '#e2b85a',
            self::White => '#ffffff',
            self::Red => '#a5434a',
            self::Green => '#b5c9ae',
        };
    }
}
