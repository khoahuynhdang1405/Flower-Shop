<?php

namespace App\Enums;

/**
 * Loại danh mục / bộ sưu tập (categories.type).
 */
enum CategoryType: string
{
    case Season = 'season';
    case Occasion = 'occasion';
    case FlowerType = 'flower_type';

    /**
     * Theo mùa / Theo dịp gắn sản phẩm qua pivot category_product nên admin thêm/bỏ được.
     * Kiểu hoa là products.category_id (mỗi sản phẩm đúng một kiểu) nên chỉ xem, đổi ở form sản phẩm.
     */
    public function allowsManualProducts(): bool
    {
        return $this !== self::FlowerType;
    }

    public function label(): string
    {
        return __('admin.collections.types.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])->all();
    }
}
