<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Trạng thái xử lý đơn (orders.order_status, order_status_logs.status).
 */
enum OrderStatus: string
{
    case Received = 'received';
    case Preparing = 'preparing';
    case PreparedReady = 'prepared_ready';
    case Shipping = 'shipping';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Nhãn trạng thái trên trang quản trị.
     */
    public function adminLabel(): string
    {
        return __('admin.orders.statuses.'.$this->value);
    }

    /**
     * Nhãn trạng thái cho khách (trang tra cứu đơn).
     */
    public function customerLabel(): string
    {
        return __('order.tracking.statuses.'.$this->value);
    }

    /**
     * Vị trí trên timeline 4 bước của trang tra cứu (Đã tiếp nhận · Đang cắm hoa · Đang giao · Hoàn thành);
     * null khi đơn đã hủy.
     */
    public function trackingStep(): ?int
    {
        return match ($this) {
            self::Received => 0,
            self::Preparing, self::PreparedReady => 1,
            self::Shipping => 2,
            self::Completed => 3,
            self::Cancelled => null,
        };
    }

    /**
     * Tab danh sách đơn admin chứa trạng thái này: "Sẵn sàng giao" nằm chung tab "Đang chuẩn bị".
     */
    public function tab(): self
    {
        return $this === self::PreparedReady ? self::Preparing : $this;
    }

    /**
     * Các trạng thái hiển thị trong một tab.
     *
     * @return list<self>
     */
    public function tabStatuses(): array
    {
        return $this === self::Preparing ? [self::Preparing, self::PreparedReady] : [$this];
    }

    /**
     * Bước kế tiếp theo quy trình của tiệm; null khi đơn đã hoàn thành hoặc đã hủy.
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Received => self::Preparing,
            self::Preparing => self::PreparedReady,
            self::PreparedReady => self::Shipping,
            self::Shipping => self::Completed,
            self::Completed, self::Cancelled => null,
        };
    }

    /**
     * Tone của x-admin.badge.
     */
    public function badgeTone(): string
    {
        return match ($this) {
            self::Received => 'warning',
            self::Cancelled => 'neutral',
            default => 'success',
        };
    }

    /**
     * Các tab trạng thái theo thứ tự trên thiết kế (sau tab "Tất cả").
     *
     * @return list<self>
     */
    public static function adminTabs(): array
    {
        return [self::Received, self::Preparing, self::Shipping, self::Completed, self::Cancelled];
    }
}
