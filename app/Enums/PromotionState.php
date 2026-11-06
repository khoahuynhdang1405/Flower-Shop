<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Tình trạng mã ưu đãi khách đã nhập ở giỏ hàng.
 */
enum PromotionState: string
{
    /** Đang được trừ vào đơn. */
    case Applied = 'applied';

    /** Mã còn dùng được nhưng tạm tính chưa đủ giá trị tối thiểu. */
    case BelowMinimum = 'below_minimum';

    /** Mã đã bị tắt hoặc không còn. */
    case Unavailable = 'unavailable';
}
