<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Customer\AdminCustomerFilters;
use App\Enums\CustomerGroup;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    /**
     * Danh sách khách cho Admin › Khách hàng, kèm orders_count (đơn không hủy), spent (tổng đơn đã hoàn thành)
     * và last_order_at; tìm theo tên, email, SĐT hoặc mã khách (KH0012).
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginateCustomersForAdmin(AdminCustomerFilters $filters, int $perPage): LengthAwarePaginator;

    public function countAll(): int;

    public function countCreatedBetween(CarbonInterface $from, CarbonInterface $to): int;

    /**
     * Số khách có từ $minOrders đơn (không tính đơn đã hủy) trở lên.
     */
    public function countWithAtLeastOrders(int $minOrders): int;

    public function countInGroup(CustomerGroup $group): int;

    public function findById(int $id): ?User;

    /**
     * Tổng total_amount các đơn đã hoàn thành của khách.
     */
    public function sumCompletedSpending(int $userId): int;

    public function assignGroup(User $user, CustomerGroup $group): void;

    /**
     * Khách kèm số liệu đơn (như danh sách) và $recentOrders đơn mới nhất (kèm món hoa) cho trang chi tiết.
     */
    public function loadOrderHistory(User $user, int $recentOrders): User;

    /**
     * @param  array{name: string, phone: ?string, birthday: ?string, customer_group: CustomerGroup}  $attributes
     */
    public function update(User $user, array $attributes): void;
}
