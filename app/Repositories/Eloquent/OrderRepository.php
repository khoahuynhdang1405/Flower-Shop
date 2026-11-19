<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Order\AdminOrderFilters;
use App\Enums\CartItemType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $attributes): Order
    {
        return Order::create($attributes);
    }

    public function assignOrderCode(Order $order, string $orderCode): void
    {
        $order->update(['order_code' => $orderCode]);
    }

    public function createDeliveryDetail(Order $order, array $attributes): void
    {
        $order->deliveryDetail()->create($attributes);
    }

    public function createItems(Order $order, array $items): void
    {
        $order->items()->createMany($items);
    }

    public function createStatusLog(Order $order, array $attributes): void
    {
        $order->statusLogs()->create($attributes);
    }

    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findWithDetailsById(int $id): ?Order
    {
        $order = Order::query()->with(['deliveryDetail', 'items', 'promotion'])->find($id);

        // Ảnh chỉ lấy cho dòng hoa: item_id của dòng quà kèm trỏ bảng addons.
        $order?->items->where('item_type', CartItemType::Flower)->load('product.primaryImage');

        return $order;
    }

    public function findForTracking(int $userId, string $orderCode, array $senderPhones): ?Order
    {
        $order = Order::query()
            ->where('user_id', $userId)
            ->where('order_code', $orderCode)
            ->whereHas('deliveryDetail', fn (Builder $detail) => $detail->whereIn('sender_phone', $senderPhones))
            ->with(['deliveryDetail', 'items', 'statusLogs' => fn (HasMany $logs) => $logs->latest()->orderByDesc('id')])
            ->first();

        $order?->items->where('item_type', CartItemType::Flower)->load('product.primaryImage');

        return $order;
    }

    public function markPaymentPaid(Order $order, ?string $transactionRef): bool
    {
        $attributes = ['payment_status' => PaymentStatus::Paid, 'transaction_ref' => $transactionRef];

        // Update có điều kiện trong một câu lệnh: hai request cùng lúc thì chỉ một request đổi được dòng.
        $changed = Order::query()
            ->whereKey($order->id)
            ->where('payment_status', '!=', PaymentStatus::Paid)
            ->update($attributes) === 1;

        $order->forceFill($attributes)->syncOriginal();

        return $changed;
    }

    public function markPaymentFailed(Order $order): void
    {
        $order->update(['payment_status' => PaymentStatus::Failed]);
    }

    public function paginateForAdmin(AdminOrderFilters $filters, int $perPage): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->when($filters->tab, fn (Builder $query, OrderStatus $tab) => $query->whereIn('order_status', $tab->tabStatuses()))
            ->with([
                'deliveryDetail',
                'items' => fn (HasMany $items) => $items->where('item_type', CartItemType::Flower)->orderBy('id'),
            ])
            ->latest()
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countByStatus(AdminOrderFilters $filters): array
    {
        return $this->countGroupedByStatus($this->filteredQuery($filters));
    }

    public function countByStatusCreatedBetween(CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->countGroupedByStatus(Order::query()->whereBetween('created_at', [$from, $to]));
    }

    public function countCreatedBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        return Order::query()->whereBetween('created_at', [$from, $to])->count();
    }

    public function countInStatus(OrderStatus $status): int
    {
        return Order::query()->where('order_status', $status)->count();
    }

    public function summarizeCompletedOrdersByDay(CarbonInterface $from, CarbonInterface $to): array
    {
        return Order::query()
            ->join('order_status_logs as completion', 'completion.order_id', '=', 'orders.id')
            ->where('completion.status', OrderStatus::Completed)
            ->where('orders.order_status', OrderStatus::Completed)
            ->whereBetween('completion.created_at', [$from, $to])
            ->selectRaw('date(completion.created_at) as day, sum(orders.total_amount) as revenue, count(distinct orders.id) as orders_count')
            ->groupByRaw('date(completion.created_at)')
            ->get()
            ->mapWithKeys(fn (Order $day): array => [
                substr((string) $day->getAttribute('day'), 0, 10) => [
                    'revenue' => (int) round((float) $day->getAttribute('revenue')),
                    'orders' => (int) $day->getAttribute('orders_count'),
                ],
            ])
            ->all();
    }

    public function sumFlowersToDeliver(CarbonInterface $deliveryDate, array $timeSlots): int
    {
        $query = $this->schedulableQuery($deliveryDate)
            ->whereIn('delivery.time_slot', $timeSlots)
            ->where('orders.order_status', '!=', OrderStatus::Completed)
            ->join('order_items as items', 'items.order_id', '=', 'orders.id')
            ->where('items.item_type', CartItemType::Flower);

        return (int) $query->sum('items.quantity');
    }

    public function findLatestWithSummary(int $limit): Collection
    {
        return Order::query()
            ->with([
                'deliveryDetail',
                'items' => fn (HasMany $items) => $items->where('item_type', CartItemType::Flower)->orderBy('id'),
            ])
            ->latest()
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function findForFloristSchedule(CarbonInterface $deliveryDate): Collection
    {
        return $this->schedulableQuery($deliveryDate)
            ->select('orders.*')
            ->with([
                'deliveryDetail',
                'items' => fn (HasMany $items) => $items->orderByRaw('item_type = ? desc', [CartItemType::Flower->value])->orderBy('id'),
            ])
            ->orderBy('delivery.time_slot')
            ->orderBy('orders.id')
            ->get();
    }

    public function transitionStatus(Order $order, OrderStatus $from, OrderStatus $to): bool
    {
        // Update có điều kiện: hai nhân viên bấm cùng lúc thì chỉ một người đổi được dòng.
        $changed = Order::query()
            ->whereKey($order->id)
            ->where('order_status', $from)
            ->update(['order_status' => $to]) === 1;

        if ($changed) {
            $order->forceFill(['order_status' => $to])->syncOriginal();
        }

        return $changed;
    }

    /**
     * Tìm theo mã đơn, tên hoặc SĐT người đặt (không phân biệt hoa thường) + lọc trạng thái thanh toán.
     *
     * @return Builder<Order>
     */
    private function filteredQuery(AdminOrderFilters $filters): Builder
    {
        return Order::query()
            ->when($filters->payment, fn (Builder $query, PaymentStatus $payment) => $query->where('payment_status', $payment))
            ->when($filters->search, function (Builder $query, string $search): void {
                $pattern = '%'.$search.'%';

                $query->where(fn (Builder $match) => $match
                    ->whereLike('order_code', $pattern)
                    ->orWhereHas('deliveryDetail', fn (Builder $detail) => $detail
                        ->whereLike('sender_name', $pattern)
                        ->orWhereLike('sender_phone', $pattern)));
            });
    }

    /**
     * Đơn cần giao trong ngày: bỏ đơn đã hủy và đơn VNPAY/MoMo chưa thanh toán.
     *
     * @return Builder<Order>
     */
    private function schedulableQuery(CarbonInterface $deliveryDate): Builder
    {
        $onlineMethods = array_values(array_filter(PaymentMethod::cases(), fn (PaymentMethod $method): bool => $method->isOnline()));

        return Order::query()
            ->join('order_delivery_details as delivery', 'delivery.order_id', '=', 'orders.id')
            ->whereDate('delivery.delivery_date', $deliveryDate->toDateString())
            ->where('orders.order_status', '!=', OrderStatus::Cancelled)
            ->where(fn (Builder $payable) => $payable
                ->where('orders.payment_status', PaymentStatus::Paid)
                ->orWhereNotIn('orders.payment_method', $onlineMethods));
    }

    /**
     * @param  Builder<Order>  $query
     * @return array<string, int>
     */
    private function countGroupedByStatus(Builder $query): array
    {
        return $query->toBase()
            ->select('order_status')
            ->selectRaw('count(*) as aggregate')
            ->groupBy('order_status')
            ->pluck('aggregate', 'order_status')
            ->map(fn (mixed $count): int => (int) $count)
            ->all();
    }
}
