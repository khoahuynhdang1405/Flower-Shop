<?php

declare(strict_types=1);

namespace App\DTOs\Dashboard;

use Carbon\CarbonImmutable;

/**
 * Một cột của biểu đồ doanh thu: ngày và doanh thu (VNĐ) của đơn hoàn thành trong ngày đó.
 */
final readonly class RevenueDay
{
    public function __construct(
        public CarbonImmutable $date,
        public int $amount,
    ) {}
}
