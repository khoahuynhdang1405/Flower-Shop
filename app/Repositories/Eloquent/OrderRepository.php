<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\CartItemType;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

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
}
