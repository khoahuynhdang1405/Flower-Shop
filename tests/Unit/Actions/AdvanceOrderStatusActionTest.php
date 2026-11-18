<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Customer\UpgradeCustomerGroupAction;
use App\Actions\Order\AdvanceOrderStatusAction;
use App\Enums\CustomerGroup;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Chuyển đơn sang bước kế tiếp, với repository giả.
 */
class AdvanceOrderStatusActionTest extends TestCase
{
    private OrderRepositoryInterface&MockInterface $orders;

    private UserRepositoryInterface&MockInterface $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orders = Mockery::mock(OrderRepositoryInterface::class);
        $this->users = Mockery::mock(UserRepositoryInterface::class);
    }

    public function test_moves_order_to_next_step_and_logs_the_admin(): void
    {
        $order = $this->order(OrderStatus::Received);
        $this->orders->expects('transitionStatus')->with($order, OrderStatus::Received, OrderStatus::Preparing)->andReturn(true);
        $this->orders->expects('createStatusLog')->with($order, Mockery::on(fn (array $log): bool => $log['admin_id'] === 7
            && $log['status'] === OrderStatus::Preparing
            && $log['title'] === __('order.status_logs.preparing', locale: 'vi')));
        $this->users->expects('findById')->never();

        $this->assertTrue($this->action()->execute($order, 7));
    }

    public function test_completing_a_customer_order_reviews_their_group(): void
    {
        $order = $this->order(OrderStatus::Shipping, userId: 5);
        $customer = (new User)->forceFill(['id' => 5, 'customer_group' => CustomerGroup::NewFriend]);
        $this->orders->expects('transitionStatus')->andReturn(true);
        $this->orders->expects('createStatusLog');
        $this->users->expects('findById')->with(5)->andReturn($customer);
        $this->users->expects('sumCompletedSpending')->with(5)->andReturn(12_000_000);
        $this->users->expects('assignGroup')->with($customer, CustomerGroup::Loyal);

        $this->assertTrue($this->action()->execute($order, 7));
    }

    public function test_completing_a_guest_order_skips_the_group_review(): void
    {
        $this->orders->expects('transitionStatus')->andReturn(true);
        $this->orders->expects('createStatusLog');
        $this->users->expects('findById')->never();

        $this->assertTrue($this->action()->execute($this->order(OrderStatus::Shipping), 7));
    }

    public function test_writes_no_log_when_another_admin_changed_the_order_first(): void
    {
        $order = $this->order(OrderStatus::Shipping, userId: 5);
        $this->orders->expects('transitionStatus')->with($order, OrderStatus::Shipping, OrderStatus::Completed)->andReturn(false);
        $this->orders->expects('createStatusLog')->never();
        $this->users->expects('findById')->never();

        $this->assertFalse($this->action()->execute($order, 7));
    }

    public function test_finished_orders_have_no_next_step(): void
    {
        $this->orders->expects('transitionStatus')->never();

        $action = $this->action();

        $this->assertFalse($action->execute($this->order(OrderStatus::Completed), 7));
        $this->assertFalse($action->execute($this->order(OrderStatus::Cancelled), 7));
    }

    private function action(): AdvanceOrderStatusAction
    {
        return new AdvanceOrderStatusAction($this->orders, new UpgradeCustomerGroupAction($this->users));
    }

    private function order(OrderStatus $status, ?int $userId = null): Order
    {
        return (new Order)->forceFill(['id' => 12, 'user_id' => $userId, 'order_status' => $status]);
    }
}
