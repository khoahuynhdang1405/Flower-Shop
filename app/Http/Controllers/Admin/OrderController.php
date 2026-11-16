<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Order\AdvanceOrderStatusAction;
use App\Actions\Order\BuildFloristScheduleAction;
use App\Actions\Order\ListAdminOrdersAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListOrdersRequest;
use App\Http\Requests\Admin\ShowFloristScheduleRequest;
use App\Models\Order;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin › Đơn hàng: danh sách chia tab theo trạng thái và chuyển đơn sang bước kế tiếp.
 */
class OrderController extends Controller
{
    public function index(ListOrdersRequest $request, ListAdminOrdersAction $listOrders): View
    {
        $filters = $request->toFilters();

        return view('admin.orders.index', [
            ...$listOrders->execute($filters, CarbonImmutable::now()),
            'filters' => $filters,
        ]);
    }

    public function schedule(ShowFloristScheduleRequest $request, BuildFloristScheduleAction $buildSchedule): View
    {
        $date = $request->deliveryDate();

        return view('admin.orders.schedule', [
            'date' => $date,
            'slots' => $buildSchedule->execute($date),
        ]);
    }

    public function advance(Order $order, AdvanceOrderStatusAction $advanceStatus): RedirectResponse
    {
        $from = $order->order_status;

        if (! $advanceStatus->execute($order, (int) auth('admin')->id())) {
            return back()->with('error', __('admin.orders.flash.not_advanced', ['code' => $order->order_code]));
        }

        return back()->with('status', __('admin.orders.flash.advanced', [
            'code' => $order->order_code,
            'from' => $from->adminLabel(),
            'to' => $order->order_status->adminLabel(),
        ]));
    }
}
