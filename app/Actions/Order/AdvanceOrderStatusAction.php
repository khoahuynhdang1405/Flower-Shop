<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Đưa đơn sang bước kế tiếp (Chờ xác nhận → Đang chuẩn bị → Sẵn sàng giao → Đang giao → Hoàn thành)
 * và ghi nhật ký kèm nhân viên thực hiện. Trả về false khi đơn không còn bước kế hoặc đã được người khác đổi.
 */
final class AdvanceOrderStatusAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function execute(Order $order, int $adminId): bool
    {
        $current = $order->order_status;
        $next = $current->next();

        if ($next === null) {
            return false;
        }

        return DB::transaction(function () use ($order, $adminId, $current, $next): bool {
            if (! $this->orders->transitionStatus($order, $current, $next)) {
                return false;
            }

            $this->orders->createStatusLog($order, [
                'admin_id' => $adminId,
                'status' => $next,
                'title' => __('order.status_logs.'.$next->value, locale: 'vi'),
                'description' => __('order.status_logs.'.$next->value.'_description', locale: 'vi'),
            ]);

            return true;
        });
    }
}
