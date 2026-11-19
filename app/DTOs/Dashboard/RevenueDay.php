<?php

declare(strict_types=1);

namespace App\DTOs\Dashboard;

use Carbon\CarbonImmutable;

/**
 * Một cột của biểu đồ Tổng quan: doanh thu (VNĐ) và số đơn đã hoàn thành trong ngày.
 */
final readonly class RevenueDay
{
    public function __construct(
        public CarbonImmutable $date,
        public int $amount,
        public int $orders,
    ) {}
}
