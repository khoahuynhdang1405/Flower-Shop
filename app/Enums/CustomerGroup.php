<?php

namespace App\Enums;

/**
 * Nhóm khách hiển thị ở trang Tài khoản và Admin › Khách hàng. Khách không tự đổi; admin gán.
 */
enum CustomerGroup: string
{
    case NewFriend = 'new';
    case Loyal = 'loyal';
    case BestFriend = 'best_friend';

    public function label(): string
    {
        return __('account.groups.'.$this->value);
    }
}
