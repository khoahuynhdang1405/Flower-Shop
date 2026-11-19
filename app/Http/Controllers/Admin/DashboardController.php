<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Dashboard\ShowAdminDashboardAction;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Trang Tổng quan của không gian quản trị.
     */
    public function __invoke(ShowAdminDashboardAction $showDashboard): View
    {
        $today = CarbonImmutable::now()->locale(app()->getLocale());

        return view('admin.dashboard', [
            ...$showDashboard->execute($today),
            'description' => __('admin.dashboard.description', [
                'weekday' => Str::title($today->isoFormat('dddd')),
                'date' => $today->isoFormat(__('admin.formats.date_long')),
            ]),
            'todayLabel' => __('admin.dashboard.today', [
                'date' => $today->isoFormat(__('admin.formats.date_short')),
            ]),
        ]);
    }
}
