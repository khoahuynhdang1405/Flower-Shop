<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Dashboard\BuildRevenueChartAction;
use App\DTOs\Dashboard\RevenueDay;
use App\Enums\ChartRange;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Biểu đồ Tổng quan với repository giả: đủ cột cho 7 / 30 ngày, doanh thu + số đơn, so sánh với khoảng liền trước.
 */
class BuildRevenueChartActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private OrderRepositoryInterface&MockInterface $orders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orders = Mockery::mock(OrderRepositoryInterface::class);
    }

    public function test_builds_seven_days_ending_today_with_zero_for_quiet_days(): void
    {
        $this->orders->expects('summarizeCompletedOrdersByDay')
            ->withArgs(fn (CarbonInterface $from, CarbonInterface $to): bool => $from->format('Y-m-d H:i:s') === '2026-09-24 00:00:00'
                && $to->format('Y-m-d H:i:s') === '2026-10-07 23:59:59')
            ->andReturn([
                '2026-10-01' => ['revenue' => 8200000, 'orders' => 3],
                '2026-10-07' => ['revenue' => 12450000, 'orders' => 5],
            ]);

        $chart = $this->action()->execute(CarbonImmutable::parse('2026-10-07 15:30:00'));

        $this->assertSame(
            ['01/10', '02/10', '03/10', '04/10', '05/10', '06/10', '07/10'],
            array_map(fn (RevenueDay $day): string => $day->date->format('d/m'), $chart->days),
        );
        $this->assertSame([8200000, 0, 0, 0, 0, 0, 12450000], array_map(fn (RevenueDay $day): int => $day->amount, $chart->days));
        $this->assertSame([3, 0, 0, 0, 0, 0, 5], array_map(fn (RevenueDay $day): int => $day->orders, $chart->days));
        $this->assertSame(20650000, $chart->total());
        $this->assertSame(8, $chart->totalOrders());
        $this->assertSame(12450000, $chart->today()->amount);
        $this->assertNull($chart->changePercent());
        $this->assertNull($chart->ordersChangePercent());
        $this->assertNull($chart->todayChangePercent());
    }

    public function test_compares_with_the_previous_period_and_yesterday(): void
    {
        $this->orders->expects('summarizeCompletedOrdersByDay')->andReturn([
            '2026-09-24' => ['revenue' => 4000000, 'orders' => 2],
            '2026-09-30' => ['revenue' => 6000000, 'orders' => 2],
            '2026-10-06' => ['revenue' => 10000000, 'orders' => 3],
            '2026-10-07' => ['revenue' => 8000000, 'orders' => 3],
        ]);

        $chart = $this->action()->execute(CarbonImmutable::parse('2026-10-07 09:00:00'));

        $this->assertSame(10000000, $chart->previousTotal);
        $this->assertSame(4, $chart->previousOrders);
        $this->assertSame(80.0, $chart->changePercent());
        $this->assertSame(50.0, $chart->ordersChangePercent());
        $this->assertSame(-20.0, $chart->todayChangePercent());
    }

    public function test_thirty_day_range_reads_sixty_days_and_keeps_thirty_columns(): void
    {
        $this->orders->expects('summarizeCompletedOrdersByDay')
            ->withArgs(fn (CarbonInterface $from): bool => $from->toDateString() === '2026-08-09')
            ->andReturn([]);

        $chart = $this->action()->execute(CarbonImmutable::parse('2026-10-07'), ChartRange::Month);

        $this->assertCount(30, $chart->days);
        $this->assertSame('2026-09-08', $chart->days[0]->date->toDateString());
        $this->assertSame('2026-10-07', $chart->today()->date->toDateString());
    }

    private function action(): BuildRevenueChartAction
    {
        return new BuildRevenueChartAction($this->orders);
    }
}
