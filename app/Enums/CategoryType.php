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
