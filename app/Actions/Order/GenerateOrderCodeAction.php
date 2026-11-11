<?php

declare(strict_types=1);

namespace App\Actions\Order;

/**
 * Mã đơn theo thứ tự đặt: "#LH-0001", "#LH-0002"... lấy từ id tự tăng của đơn,
 * nên đơn đặt trước luôn có số nhỏ hơn và hai đơn đặt cùng lúc không bao giờ trùng mã.
 */
final class GenerateOrderCodeAction
{
    public function execute(int $orderId): string
    {
        return '#LH-'.str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
    }
}
