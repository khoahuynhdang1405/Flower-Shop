<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\DTOs\Dashboard\RevenueChart;
use App\DTOs\Dashboard\RevenueDay;
use App\Enums\ChartRange;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;

/**
 * Doanh thu và số đơn hoàn thành theo ngày cho N ngày gần nhất (tính cả hôm nay), kèm tổng của N ngày liền trước.
 * Ngày không có đơn hoàn thành vẫn có cột, giá trị 0.
 */
final class BuildRevenueChartAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function execute(CarbonImmutable $today, ChartRange $range = ChartRange::Week): RevenueChart
    {
        $today = $today->startOfDay();
        $length = $range->value;
        $summary = $this->orders->summarizeCompletedOrdersByDay($today->subDays($length * 2 - 1), $today->endOfDay());

        $days = [];
        $previousTotal = 0;
        $previousOrders = 0;

        for ($offset = $length * 2 - 1; $offset >= 0; $offset--) {
            $date = $today->subDays($offset);
            $day = $summary[$date->toDateString()] ?? ['revenue' => 0, 'orders' => 0];

            if ($offset >= $length) {
                $previousTotal += $day['revenue'];
                $previousOrders += $day['orders'];

                continue;
            }

            $days[] = new RevenueDay($date, $day['revenue'], $day['orders']);
        }

        return new RevenueChart($days, $previousTotal, $previousOrders);
    }
}
