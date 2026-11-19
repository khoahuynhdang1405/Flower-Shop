<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Dashboard\RevenueChart;
use App\DTOs\Dashboard\RevenueDay;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Dữ liệu biểu đồ Tổng quan cho Chart.js: mỗi ngày một cột, hai chỉ số (doanh thu VNĐ, số đơn hoàn thành).
 * Cột cuối là hôm nay. % thay đổi là null khi khoảng trước không có số liệu.
 *
 * @property RevenueChart $resource
 */
class RevenueChartResource extends JsonResource
{
    /**
     * @return array{
     *     days: int,
     *     labels: list<string>,
     *     dates: list<string>,
     *     revenue: list<int>,
     *     orders: list<int>,
     *     totals: array{revenue: int, orders: int},
     *     changes: array{revenue: float|null, orders: float|null},
     * }
     */
    public function toArray(Request $request): array
    {
        $days = $this->resource->days;

        return [
            'days' => count($days),
            'labels' => array_map(fn (RevenueDay $day): string => $day->date->format('d/m'), $days),
            'dates' => array_map(fn (RevenueDay $day): string => $day->date->format('d/m/Y'), $days),
            'revenue' => array_map(fn (RevenueDay $day): int => $day->amount, $days),
            'orders' => array_map(fn (RevenueDay $day): int => $day->orders, $days),
            'totals' => [
                'revenue' => $this->resource->total(),
                'orders' => $this->resource->totalOrders(),
            ],
            'changes' => [
                'revenue' => $this->resource->changePercent(),
                'orders' => $this->resource->ordersChangePercent(),
            ],
        ];
    }
}
