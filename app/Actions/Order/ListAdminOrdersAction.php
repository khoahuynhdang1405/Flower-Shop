<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\DTOs\Order\AdminOrderFilters;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Dữ liệu trang Admin › Đơn hàng: trang đơn theo bộ lọc, số đếm trên tab và 4 thẻ số liệu.
 */
final class ListAdminOrdersAction
{
    public const PER_PAGE = 8;

    public function __construct(private OrderRepositoryInterface $orders) {}

    /**
     * @return array{
     *     orders: LengthAwarePaginator<int, Order>,
     *     tabCounts: array<string, int>,
     *     stats: array{today: int, todayChange: int, received: int, preparing: int, shipping: int, completedToday: int, cancelledToday: int}
     * }
     */
    public function execute(AdminOrderFilters $filters, CarbonImmutable $now): array
    {
        $today = $now->startOfDay();
        $byStatus = $this->orders->countByStatus(new AdminOrderFilters);
        $todayByStatus = $this->orders->countByStatusCreatedBetween($today, $today->endOfDay());
        $todayCount = array_sum($todayByStatus);
        $count = fn (array $counts, OrderStatus ...$statuses): int => array_sum(array_map(fn (OrderStatus $status): int => $counts[$status->value] ?? 0, $statuses));

        return [
            'orders' => $this->orders->paginateForAdmin($filters, self::PER_PAGE),
            'tabCounts' => $this->tabCounts($this->orders->countByStatus(new AdminOrderFilters(search: $filters->search, payment: $filters->payment))),
            'stats' => [
                'today' => $todayCount,
                'todayChange' => $todayCount - $this->orders->countCreatedBetween($today->subDay(), $today->subDay()->endOfDay()),
                'received' => $count($byStatus, OrderStatus::Received),
                'preparing' => $count($byStatus, OrderStatus::Preparing, OrderStatus::PreparedReady),
                'shipping' => $count($byStatus, OrderStatus::Shipping),
                'completedToday' => $count($todayByStatus, OrderStatus::Completed),
                'cancelledToday' => $count($todayByStatus, OrderStatus::Cancelled),
            ],
        ];
    }

    /**
     * Gộp số đếm theo trạng thái thành số đếm theo tab; khóa '' là tab "Tất cả".
     *
     * @param  array<string, int>  $byStatus
     * @return array<string, int>
     */
    private function tabCounts(array $byStatus): array
    {
        $counts = ['' => array_sum($byStatus)];

        foreach (OrderStatus::adminTabs() as $tab) {
            $counts[$tab->value] = array_sum(array_map(fn (OrderStatus $status): int => $byStatus[$status->value] ?? 0, $tab->tabStatuses()));
        }

        return $counts;
    }
}
