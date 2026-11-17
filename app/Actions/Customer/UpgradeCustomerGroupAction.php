<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Enums\CustomerGroup;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Tự nâng nhóm khách theo tổng chi tiêu (chỉ tính đơn đã hoàn thành):
 * từ 10.000.000 đ → Khách thân thiết, từ 20.000.000 đ → Bạn thân của Lá.
 * Chỉ nâng, không hạ: nhóm admin đã gán cao hơn được giữ nguyên.
 */
final class UpgradeCustomerGroupAction
{
    public const LOYAL_FROM = 10_000_000;

    public const BEST_FRIEND_FROM = 20_000_000;

    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @return CustomerGroup|null nhóm mới khi vừa được nâng, null khi giữ nguyên
     */
    public function execute(int $userId): ?CustomerGroup
    {
        $customer = $this->users->findById($userId);

        if ($customer === null) {
            return null;
        }

        $earned = $this->groupForSpending($this->users->sumCompletedSpending($userId));

        if ($earned->rank() <= $customer->customer_group->rank()) {
            return null;
        }

        $this->users->assignGroup($customer, $earned);

        return $earned;
    }

    private function groupForSpending(int $spent): CustomerGroup
    {
        return match (true) {
            $spent >= self::BEST_FRIEND_FROM => CustomerGroup::BestFriend,
            $spent >= self::LOYAL_FROM => CustomerGroup::Loyal,
            default => CustomerGroup::NewFriend,
        };
    }
}
