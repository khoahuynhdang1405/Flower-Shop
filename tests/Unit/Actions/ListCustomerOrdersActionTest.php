<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Order\ListCustomerOrdersAction;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Những lần gửi thương ở trang Tài khoản với repository giả: chỉ lấy đơn của đúng tài khoản.
 */
class ListCustomerOrdersActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_returns_the_orders_of_the_given_customer(): void
    {
        $orders = new Collection([new Order]);
        $repository = Mockery::mock(OrderRepositoryInterface::class);
        $repository->expects('findByCustomer')->with(7)->andReturn($orders);

        $this->assertSame($orders, (new ListCustomerOrdersAction($repository))->execute(7));
    }
}
