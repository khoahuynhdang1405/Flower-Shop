<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\DTOs\Customer\AdminCustomerFilters;
use App\Enums\CustomerGroup;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Dữ liệu trang Admin › Khách hàng: trang khách theo bộ lọc và 4 thẻ số liệu.
 * "Khách quay lại" = khách có từ 2 đơn (không tính đơn đã hủy) trở lên.
 */
final class ListAdminCustomersAction
{
    public const PER_PAGE = 8;

    public const RETURNING_MIN_ORDERS = 2;

    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @return array{
     *     customers: LengthAwarePaginator<int, User>,
     *     stats: array{total: int, newToday: int, newTodayChange: int, returning: int, returningPercent: int, bestFriends: int}
     * }
     */
    public function execute(AdminCustomerFilters $filters, CarbonImmutable $now): array
    {
        $today = $now->startOfDay();
        $total = $this->users->countAll();
        $newToday = $this->users->countCreatedBetween($today, $today->endOfDay());
        $returning = $this->users->countWithAtLeastOrders(self::RETURNING_MIN_ORDERS);

        return [
            'customers' => $this->users->paginateCustomersForAdmin($filters, self::PER_PAGE),
            'stats' => [
                'total' => $total,
                'newToday' => $newToday,
                'newTodayChange' => $newToday - $this->users->countCreatedBetween($today->subDay(), $today->subDay()->endOfDay()),
                'returning' => $returning,
                'returningPercent' => $total === 0 ? 0 : (int) round($returning * 100 / $total),
                'bestFriends' => $this->users->countInGroup(CustomerGroup::BestFriend),
            ],
        ];
    }
}
