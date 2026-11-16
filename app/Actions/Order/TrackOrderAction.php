<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

/**
 * Tra cứu đơn của chính khách đang đăng nhập bằng mã đơn + SĐT người đặt.
 * Đơn của tài khoản khác trả null như đơn không tồn tại.
 */
final class TrackOrderAction
{
    public function __construct(private OrderRepositoryInterface $orders) {}

    public function execute(int $userId, string $orderCode, string $phone): ?Order
    {
        return $this->orders->findForTracking($userId, $orderCode, $this->phoneVariants($phone));
    }

    /**
     * Lúc đặt khách có thể ghi "0903…" hoặc "+84903…": tra bằng cách viết nào cũng khớp.
     *
     * @return list<string>
     */
    private function phoneVariants(string $phone): array
    {
        $local = str_starts_with($phone, '+84') ? '0'.substr($phone, 3) : $phone;

        return [$local, '+84'.substr($local, 1)];
    }
}
