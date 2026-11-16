<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Order\BuildFloristScheduleAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderDeliveryDetail;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Config\Repository as Config;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Gom đơn theo khung giờ giao cho lịch cắm hoa, với repository giả.
 */
class BuildFloristScheduleActionTest extends TestCase
{
    private OrderRepositoryInterface&MockInterface $orders;

    private CarbonImmutable $date;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orders = Mockery::mock(OrderRepositoryInterface::class);
        $this->date = CarbonImmutable::parse('2026-11-16');
    }

    public function test_groups_orders_by_slot_in_shop_order_and_keeps_empty_slots(): void
    {
        $morning = $this->order(1, '08:00-10:00', OrderStatus::Received);
        $afternoonA = $this->order(2, '14:00-16:00', OrderStatus::Preparing);
        $afternoonB = $this->order(3, '14:00-16:00', OrderStatus::PreparedReady);
        $this->orders->expects('findForFloristSchedule')->with($this->date)->andReturn(new Collection([$morning, $afternoonA, $afternoonB]));

        $schedule = $this->action(['08:00-10:00', '10:00-12:00', '14:00-16:00'])->execute($this->date);

        $this->assertSame(['08:00-10:00', '10:00-12:00', '14:00-16:00'], array_column($schedule, 'slot'));
        $this->assertSame([1], $schedule[0]['orders']->pluck('id')->all());
        $this->assertTrue($schedule[1]['orders']->isEmpty());
        $this->assertSame([2, 3], $schedule[2]['orders']->pluck('id')->all());
    }

    public function test_counts_orders_already_arranged(): void
    {
        $this->orders->expects('findForFloristSchedule')->andReturn(new Collection([
            $this->order(1, '08:00-10:00', OrderStatus::Received),
            $this->order(2, '08:00-10:00', OrderStatus::Preparing),
            $this->order(3, '08:00-10:00', OrderStatus::PreparedReady),
            $this->order(4, '08:00-10:00', OrderStatus::Shipping),
            $this->order(5, '08:00-10:00', OrderStatus::Completed),
        ]));

        $schedule = $this->action(['08:00-10:00'])->execute($this->date);

        $this->assertSame(3, $schedule[0]['arranged']);
    }

    public function test_appends_slots_missing_from_config_at_the_end(): void
    {
        $this->orders->expects('findForFloristSchedule')->andReturn(new Collection([
            $this->order(1, '18:00-20:00', OrderStatus::Received),
        ]));

        $schedule = $this->action(['08:00-10:00'])->execute($this->date);

        $this->assertSame(['08:00-10:00', '18:00-20:00'], array_column($schedule, 'slot'));
        $this->assertSame([1], $schedule[1]['orders']->pluck('id')->all());
    }

    /**
     * @param  list<string>  $slots
     */
    private function action(array $slots): BuildFloristScheduleAction
    {
        return new BuildFloristScheduleAction($this->orders, new Config(['shop' => ['delivery_time_slots' => $slots]]));
    }

    private function order(int $id, string $slot, OrderStatus $status): Order
    {
        $order = (new Order)->forceFill(['id' => $id, 'order_status' => $status]);
        $order->setRelation('deliveryDetail', (new OrderDeliveryDetail)->forceFill(['time_slot' => $slot]));

        return $order;
    }
}
