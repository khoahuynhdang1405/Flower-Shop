<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Những lần gửi thương của khách đang đăng nhập (trang Tài khoản), mới nhất trước.
 */
final class ListCustomerOrdersAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    /**
     * @return Collection<int, Order>
     */
    public function execute(int $userId): Collection
    {
        return $this->orders->findByCustomer($userId);
    }
}
