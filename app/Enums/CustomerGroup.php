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

    /**
     * Thứ bậc gắn bó (khách mới < thân thiết < bạn thân), để chỉ nâng nhóm chứ không hạ.
     */
    public function rank(): int
    {
        return array_search($this, self::cases(), true);
    }

    /**
     * Màu nhãn ở Admin › Khách hàng: khách mới xám, khách thân thiết / bạn thân xanh nhạt.
     */
    public function badgeTone(): string
    {
        return $this === self::NewFriend ? 'neutral' : 'success';
    }

    /**
     * @return array<string, string> value => nhãn
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $group): array => [$group->value => $group->label()])->all();
    }
}
