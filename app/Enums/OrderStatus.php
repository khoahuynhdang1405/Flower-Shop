<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Trạng thái xử lý đơn (orders.order_status, order_status_logs.status).
 */
enum OrderStatus: string
{
    case Received = 'received';
    case Preparing = 'preparing';
    case PreparedReady = 'prepared_ready';
    case Shipping = 'shipping';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
