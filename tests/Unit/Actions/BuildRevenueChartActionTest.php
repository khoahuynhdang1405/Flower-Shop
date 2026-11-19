<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Dashboard\BuildRevenueChartAction;
use App\DTOs\Dashboard\RevenueDay;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Biểu đồ doanh thu 7 ngày với repository giả: đủ 7 cột, so sánh với 7 ngày trước.
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
        $this->orders->expects('sumCompletedRevenueByDay')
            ->withArgs(fn (CarbonInterface $from, CarbonInterface $to): bool => $from->format('Y-m-d H:i:s') === '2026-09-24 00:00:00'
                && $to->format('Y-m-d H:i:s') === '2026-10-07 23:59:59')
            ->andReturn(['2026-10-01' => 8200000, '2026-10-07' => 12450000]);

        $chart = $this->action()->execute(CarbonImmutable::parse('2026-10-07 15:30:00'));

        $this->assertSame(
            ['01/10', '02/10', '03/10', '04/10', '05/10', '06/10', '07/10'],
            array_map(fn (RevenueDay $day): string => $day->date->format('d/m'), $chart->days),
        );
        $this->assertSame([8200000, 0, 0, 0, 0, 0, 12450000], array_map(fn (RevenueDay $day): int => $day->amount, $chart->days));
        $this->assertSame(20650000, $chart->total());
        $this->assertSame(12450000, $chart->highest());
        $this->assertSame(12450000, $chart->today()->amount);
        $this->assertNull($chart->changePercent());
        $this->assertNull($chart->todayChangePercent());
    }

    public function test_compares_with_the_previous_seven_days_and_yesterday(): void
    {
        $this->orders->expects('sumCompletedRevenueByDay')->andReturn([
            '2026-09-24' => 4000000,
            '2026-09-30' => 6000000,
            '2026-10-06' => 10000000,
            '2026-10-07' => 8000000,
        ]);

        $chart = $this->action()->execute(CarbonImmutable::parse('2026-10-07 09:00:00'));

        $this->assertSame(10000000, $chart->previousTotal);
        $this->assertSame(18000000, $chart->total());
        $this->assertSame(80.0, $chart->changePercent());
        $this->assertSame(-20.0, $chart->todayChangePercent());
    }

    private function action(): BuildRevenueChartAction
    {
        return new BuildRevenueChartAction($this->orders);
    }
}
