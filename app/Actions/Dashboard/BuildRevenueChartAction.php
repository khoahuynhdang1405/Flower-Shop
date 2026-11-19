<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\DTOs\Dashboard\RevenueChart;
use App\DTOs\Dashboard\RevenueDay;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;

/**
 * Doanh thu 7 ngày gần nhất (tính cả hôm nay) theo ngày hoàn thành đơn, kèm tổng 7 ngày liền trước.
 * Ngày không có đơn hoàn thành vẫn có cột, giá trị 0.
 */
final class BuildRevenueChartAction
{
    public const DAYS = 7;

    public function __construct(private OrderRepositoryInterface $orders) {}

    public function execute(CarbonImmutable $today): RevenueChart
    {
        $today = $today->startOfDay();
        $revenueByDay = $this->orders->sumCompletedRevenueByDay(
            $today->subDays(self::DAYS * 2 - 1),
            $today->endOfDay(),
        );

        $days = [];
        $previousTotal = 0;

        for ($offset = self::DAYS * 2 - 1; $offset >= 0; $offset--) {
            $date = $today->subDays($offset);
            $amount = $revenueByDay[$date->toDateString()] ?? 0;

            if ($offset >= self::DAYS) {
                $previousTotal += $amount;
                continue;
            }

            $days[] = new RevenueDay($date, $amount);
        }

        return new RevenueChart($days, $previousTotal);
    }
}
