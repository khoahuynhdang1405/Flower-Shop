<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Collection;

/**
 * Lịch cắm hoa của một ngày giao: gom đơn theo khung giờ của tiệm, khung trống vẫn hiện.
 */
final class BuildFloristScheduleAction
{
    /**
     * Các trạng thái coi như bó hoa đã cắm xong.
     */
    private const ARRANGED = [OrderStatus::PreparedReady, OrderStatus::Shipping, OrderStatus::Completed];

    public function __construct(
        private OrderRepositoryInterface $orders,
        private Config $config,
    ) {}

    /**
     * @return list<array{slot: string, orders: Collection<int, Order>, arranged: int}>
     */
    public function execute(CarbonImmutable $deliveryDate): array
    {
        $bySlot = $this->orders->findForFloristSchedule($deliveryDate)
            ->toBase()
            ->groupBy(fn (Order $order): string => $order->deliveryDetail->time_slot);

        // Khung giờ của tiệm theo thứ tự trong ngày; khung lạ (dữ liệu cũ) nối vào cuối.
        $slots = collect($this->config->get('shop.delivery_time_slots'))
            ->merge($bySlot->keys())
            ->unique()
            ->values();

        return $slots->map(function (string $slot) use ($bySlot): array {
            $orders = $bySlot->get($slot, collect())->values();

            return [
                'slot' => $slot,
                'orders' => $orders,
                'arranged' => $orders->filter(fn (Order $order): bool => in_array($order->order_status, self::ARRANGED, true))->count(),
            ];
        })->all();
    }
}
