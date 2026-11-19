<?php

declare(strict_types=1);

namespace App\DTOs\Dashboard;

/**
 * Biểu đồ "Doanh thu 7 ngày qua": các ngày theo thứ tự cũ → mới (ngày cuối là hôm nay)
 * và tổng của 7 ngày liền trước để so sánh.
 */
final readonly class RevenueChart
{
    /**
     * @param  list<RevenueDay>  $days
     */
    public function __construct(
        public array $days,
        public int $previousTotal,
    ) {}

    public function total(): int
    {
        return array_sum(array_map(fn (RevenueDay $day): int => $day->amount, $this->days));
    }

    public function highest(): int
    {
        return max(array_map(fn (RevenueDay $day): int => $day->amount, $this->days));
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
     * % thay đổi của 7 ngày này so với 7 ngày trước; null khi kỳ trước không có doanh thu.
     */
    public function changePercent(): ?float
    {
        return self::percentChange($this->total(), $this->previousTotal);
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
