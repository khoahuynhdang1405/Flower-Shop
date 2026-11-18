<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Customer\AdminCustomerFilters;
use App\Enums\CartItemType;
use App\Enums\CustomerGroup;
use App\Enums\CustomerSort;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class UserRepository implements UserRepositoryInterface
{
    public function paginateCustomersForAdmin(AdminCustomerFilters $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->withOrderSummary(User::query())
            ->when($filters->group, fn (Builder $query, CustomerGroup $group) => $query->where('customer_group', $group))
            ->when($filters->search, fn (Builder $query, string $search) => $this->whereMatches($query, $search));

        match ($filters->sort) {
            CustomerSort::RecentlyUpdated => $query->latest('updated_at'),
            CustomerSort::Newest => $query->latest('created_at'),
            CustomerSort::TopSpending => $query->orderByDesc('spent'),
            CustomerSort::MostOrders => $query->orderByDesc('orders_count'),
        };

        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function countAll(): int
    {
        return User::query()->count();
    }

    public function countCreatedBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        return User::query()->whereBetween('created_at', [$from, $to])->count();
    }

    public function countWithAtLeastOrders(int $minOrders): int
    {
        return User::query()
            ->whereHas('orders', fn (Builder $orders) => $orders->where('order_status', '!=', OrderStatus::Cancelled), '>=', $minOrders)
            ->count();
    }

    public function countInGroup(CustomerGroup $group): int
    {
        return User::query()->where('customer_group', $group)->count();
    }

    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function sumCompletedSpending(int $userId): int
    {
        return (int) Order::query()
            ->where('user_id', $userId)
            ->where('order_status', OrderStatus::Completed)
            ->sum('total_amount');
    }

    public function assignGroup(User $user, CustomerGroup $group): void
    {
        $user->forceFill(['customer_group' => $group])->save();
    }

    public function loadOrderHistory(User $user, int $recentOrders): User
    {
        return $this->withOrderSummary(User::query())->whereKey($user->id)->firstOrFail()->load([
            'orders' => fn (HasMany $orders) => $orders->latest()->orderByDesc('id')->limit($recentOrders)
                ->with(['items' => fn (HasMany $items) => $items->where('item_type', CartItemType::Flower)->orderBy('id')]),
        ]);
    }

    public function update(User $user, array $attributes): void
    {
        // customer_group không nằm trong Fillable (khách không tự đổi được) nên dùng forceFill cho thao tác của admin.
        $user->forceFill($attributes)->save();
    }

    /**
     * Thêm orders_count (đơn không hủy), spent (tổng total_amount đơn đã hoàn thành, 0 khi chưa có) và last_order_at.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private function withOrderSummary(Builder $query): Builder
    {
        return $query
            ->select('users.*')
            ->withCount(['orders' => fn (Builder $orders) => $orders->where('order_status', '!=', OrderStatus::Cancelled)])
            ->addSelect(['spent' => Order::query()
                ->selectRaw('coalesce(sum(total_amount), 0)')
                ->whereColumn('orders.user_id', 'users.id')
                ->where('order_status', OrderStatus::Completed)])
            ->withMax('orders as last_order_at', 'created_at')
            ->withCasts(['spent' => 'integer', 'last_order_at' => 'datetime']);
    }

    /**
     * Tìm theo tên / email / SĐT (không phân biệt hoa thường), hoặc mã khách "KH0012" → id 12.
     *
     * @param  Builder<User>  $query
     */
    private function whereMatches(Builder $query, string $search): void
    {
        $pattern = '%'.$search.'%';

        $query->where(function (Builder $match) use ($pattern, $search): void {
            $match->whereLike('name', $pattern)
                ->orWhereLike('email', $pattern)
                ->orWhereLike('phone', '%'.preg_replace('/[\s.\-]/', '', $search).'%');

            if (preg_match('/^kh0*(\d+)$/i', $search, $code) === 1) {
                $match->orWhereKey((int) $code[1]);
            }
        });
    }
}
