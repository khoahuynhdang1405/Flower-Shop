<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Khoảng ngày của biểu đồ Tổng quan; giá trị là số ngày (tính cả hôm nay).
 */
enum ChartRange: int
{
    case Week = 7;
    case Month = 30;
}
