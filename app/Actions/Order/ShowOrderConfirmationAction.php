<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Trang "Tiệm đã nhận lời thương của bạn": đơn kèm lịch giao, các món, mã ưu đãi.
 */
final class ShowOrderConfirmationAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function execute(int $orderId): Order
    {
        return $this->orders->findWithDetailsById($orderId)
            ?? throw (new ModelNotFoundException)->setModel(Order::class, [$orderId]);
    }
}
