<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Cách sắp xếp danh sách Admin › Khách hàng; mặc định "Mới cập nhật".
 */
enum CustomerSort: string
{
    case RecentlyUpdated = 'updated';
    case Newest = 'newest';
    case TopSpending = 'spending';
    case MostOrders = 'orders';

    public function label(): string
    {
        return __('admin.customers.sorts.'.$this->value);
    }

    /**
     * @return array<string, string> value => nhãn
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $sort): array => [$sort->value => $sort->label()])->all();
    }
}
