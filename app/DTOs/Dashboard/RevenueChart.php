<?php

declare(strict_types=1);

namespace App\DTOs\Dashboard;

/**
 * Biểu đồ Tổng quan: các ngày theo thứ tự cũ → mới (ngày cuối là hôm nay)
 * và tổng của khoảng liền trước cùng độ dài để so sánh.
 */
final readonly class RevenueChart
{
    /**
     * @param  list<RevenueDay>  $days
     */
    public function __construct(
        public array $days,
        public int $previousTotal,
        public int $previousOrders,
    ) {}

    public function total(): int
    {
        return array_sum(array_map(fn (RevenueDay $day): int => $day->amount, $this->days));
    }

    public function totalOrders(): int
    {
        return array_sum(array_map(fn (RevenueDay $day): int => $day->orders, $this->days));
    }

    public function today(): RevenueDay
    {
        return $this->days[array_key_last($this->days)];
    }

    public function yesterday(): RevenueDay
    {
        return $this->days[array_key_last($this->days) - 1];
    }

    /**
     * % thay đổi doanh thu so với khoảng trước; null khi khoảng trước không có doanh thu.
     */
    public function changePercent(): ?float
    {
        return self::percentChange($this->total(), $this->previousTotal);
    }

    /**
     * % thay đổi số đơn so với khoảng trước; null khi khoảng trước không có đơn.
     */
    public function ordersChangePercent(): ?float
    {
        return self::percentChange($this->totalOrders(), $this->previousOrders);
    }

    /**
     * % thay đổi doanh thu hôm nay so với hôm qua; null khi hôm qua không có doanh thu.
     */
    public function todayChangePercent(): ?float
    {
        return self::percentChange($this->today()->amount, $this->yesterday()->amount);
    }

    private static function percentChange(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }
}
