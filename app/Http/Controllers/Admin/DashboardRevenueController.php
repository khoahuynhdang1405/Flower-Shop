<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Dashboard\BuildRevenueChartAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShowRevenueChartRequest;
use App\Http\Resources\RevenueChartResource;
use Carbon\CarbonImmutable;

/**
 * API biểu đồ Tổng quan: đổi khoảng 7 / 30 ngày không cần tải lại trang (resources/js/dashboard-chart.js).
 */
class DashboardRevenueController extends Controller
{
    public function __invoke(ShowRevenueChartRequest $request, BuildRevenueChartAction $buildChart): RevenueChartResource
    {
        return new RevenueChartResource($buildChart->execute(CarbonImmutable::now(), $request->range()));
    }
}
