<?php

declare(strict_types=1);

namespace App\Actions\Cart;

/**
 * Số trên icon giỏ ở header: số hoa + quà tính tiền còn mua được (khớp dòng "Tạm tính (n sản phẩm)").
 */
final class CountCartItemsAction
{
    public function __construct(private ListCartLinesAction $listLines) {}

    public function execute(): int
    {
        return $this->listLines->execute()->itemCount();
    }
}
