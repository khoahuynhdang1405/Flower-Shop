<?php

declare(strict_types=1);

namespace App\Actions\Cart;

/**
 * Tạm tính giỏ hàng (VNĐ) = tổng thành tiền các dòng còn mua được (xem ListCartLinesAction).
 */
final class CalculateCartSubtotalAction
{
    public function __construct(private ListCartLinesAction $listLines) {}

    public function execute(): int
    {
        return $this->listLines->execute()->subtotal();
    }
}
