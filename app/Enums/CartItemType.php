<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Loại món trong giỏ, trùng giá trị order_items.item_type.
 */
enum CartItemType: string
{
    case Flower = 'flower';
    case Addon = 'addon';
}
