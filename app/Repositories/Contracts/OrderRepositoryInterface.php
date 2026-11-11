<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Order;

interface OrderRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Order;

    public function assignOrderCode(Order $order, string $orderCode): void;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createDeliveryDetail(Order $order, array $attributes): void;

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function createItems(Order $order, array $items): void;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createStatusLog(Order $order, array $attributes): void;

    public function findById(int $id): ?Order;

    /**
     * Đơn kèm thông tin giao, các món và mã ưu đãi để hiện trang xác nhận.
     */
    public function findWithDetailsById(int $id): ?Order;

    /**
     * Đánh dấu đã thanh toán nếu đơn chưa ở trạng thái đó; trả về false khi một lần gọi khác đã làm trước.
     */
    public function markPaymentPaid(Order $order, ?string $transactionRef): bool;

    public function markPaymentFailed(Order $order): void;
}
