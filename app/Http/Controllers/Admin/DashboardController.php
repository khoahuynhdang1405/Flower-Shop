<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Trang Tổng quan của không gian quản trị.
     */
    public function __invoke(): View
    {
        $today = now()->locale(app()->getLocale());

        return view('admin.dashboard', [
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
