<?php

namespace App\Enums;

/**
 * Kiểu giảm giá của mã ưu đãi (promotions.discount_type).
 */
enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return __('admin.promotions.types.'.$this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])->all();
    }
}
