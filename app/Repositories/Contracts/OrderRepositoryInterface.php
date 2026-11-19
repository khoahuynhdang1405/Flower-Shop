<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Order\AdminOrderFilters;
use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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
     * Đơn của một tài khoản khớp mã đơn + SĐT người đặt, kèm thông tin giao, các món và nhật ký (mới nhất trước).
     *
     * @param  list<string>  $senderPhones  các cách viết của cùng một số (0… / +84…)
     */
    public function findForTracking(int $userId, string $orderCode, array $senderPhones): ?Order;

    /**
     * Đánh dấu đã thanh toán nếu đơn chưa ở trạng thái đó; trả về false khi một lần gọi khác đã làm trước.
     */
    public function markPaymentPaid(Order $order, ?string $transactionRef): bool;

    public function markPaymentFailed(Order $order): void;

    /**
     * Danh sách đơn cho trang quản trị, mới nhất trước, kèm thông tin giao và món hoa.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateForAdmin(AdminOrderFilters $filters, int $perPage): LengthAwarePaginator;

    /**
     * Số đơn theo từng trạng thái, áp dụng tìm kiếm + lọc thanh toán nhưng bỏ qua tab.
     *
     * @return array<string, int> order_status => số đơn
     */
    public function countByStatus(AdminOrderFilters $filters): array;

    /**
     * Số đơn theo từng trạng thái trong số các đơn đặt trong khoảng thời gian.
     *
     * @return array<string, int> order_status => số đơn
     */
    public function countByStatusCreatedBetween(CarbonInterface $from, CarbonInterface $to): array;

    public function countCreatedBetween(CarbonInterface $from, CarbonInterface $to): int;

    public function countInStatus(OrderStatus $status): int;

    /**
     * Doanh thu theo ngày hoàn thành: tổng total_amount của đơn đã Hoàn thành, nhóm theo ngày ghi nhật ký "completed".
     * Ngày không có đơn không xuất hiện trong kết quả.
     *
     * @return array<string, int> 'Y-m-d' => doanh thu (VNĐ)
     */
    public function sumCompletedRevenueByDay(CarbonInterface $from, CarbonInterface $to): array;

    /**
     * Số bó hoa (tổng số lượng dòng hoa) cần giao trong ngày ở các khung giờ cho trước,
     * bỏ đơn đã hủy/đã hoàn thành và đơn VNPAY/MoMo chưa thanh toán.
     *
     * @param  list<string>  $timeSlots
     */
    public function sumFlowersToDeliver(CarbonInterface $deliveryDate, array $timeSlots): int;

    /**
     * Đơn mới nhất cho Tổng quan, kèm thông tin giao và các dòng hoa.
     *
     * @return Collection<int, Order>
     */
    public function findLatestWithSummary(int $limit): Collection;

    /**
     * Đơn thợ cắm cần làm cho một ngày giao: bỏ đơn đã hủy và đơn VNPAY/MoMo chưa thanh toán.
     * Sắp theo khung giờ rồi id, kèm thông tin giao và mọi món (hoa trước, quà kèm sau).
     *
     * @return Collection<int, Order>
     */
    public function findForFloristSchedule(CarbonInterface $deliveryDate): Collection;

    /**
     * Đổi trạng thái nếu đơn vẫn đang ở $from; trả về false khi một lần gọi khác đã đổi trước.
     */
    public function transitionStatus(Order $order, OrderStatus $from, OrderStatus $to): bool;
}
