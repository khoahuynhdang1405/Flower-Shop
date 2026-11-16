<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Order\AdvanceOrderStatusAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Chuyển đơn sang bước kế tiếp, với repository giả.
 */
class AdvanceOrderStatusActionTest extends TestCase
{
    private OrderRepositoryInterface&MockInterface $orders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orders = Mockery::mock(OrderRepositoryInterface::class);
    }

    public function test_moves_order_to_next_step_and_logs_the_admin(): void
    {
        $order = $this->order(OrderStatus::Received);
        $this->orders->expects('transitionStatus')->with($order, OrderStatus::Received, OrderStatus::Preparing)->andReturn(true);
        $this->orders->expects('createStatusLog')->with($order, Mockery::on(fn (array $log): bool => $log['admin_id'] === 7
            && $log['status'] === OrderStatus::Preparing
            && $log['title'] === __('order.status_logs.preparing', locale: 'vi')));

        $this->assertTrue((new AdvanceOrderStatusAction($this->orders))->execute($order, 7));
    }

    public function test_writes_no_log_when_another_admin_changed_the_order_first(): void
    {
        $order = $this->order(OrderStatus::Shipping);
        $this->orders->expects('transitionStatus')->with($order, OrderStatus::Shipping, OrderStatus::Completed)->andReturn(false);
        $this->orders->expects('createStatusLog')->never();

        $this->assertFalse((new AdvanceOrderStatusAction($this->orders))->execute($order, 7));
    }

    public function test_finished_orders_have_no_next_step(): void
    {
        $this->orders->expects('transitionStatus')->never();

        $action = new AdvanceOrderStatusAction($this->orders);

        $this->assertFalse($action->execute($this->order(OrderStatus::Completed), 7));
        $this->assertFalse($action->execute($this->order(OrderStatus::Cancelled), 7));
    }

    private function order(OrderStatus $status): Order
    {
        return (new Order)->forceFill(['id' => 12, 'order_status' => $status]);
    }
}
