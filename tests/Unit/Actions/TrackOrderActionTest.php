<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Order\TrackOrderAction;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Mockery;
use Tests\TestCase;

/**
 * Tra cứu đơn với repository giả: SĐT được tìm theo cả hai cách viết.
 */
class TrackOrderActionTest extends TestCase
{
    public function test_looks_up_both_local_and_country_code_phone_formats(): void
    {
        $order = new Order;
        $orders = Mockery::mock(OrderRepositoryInterface::class);
        $orders->expects('findForTracking')->with(5, '#LH-0042', ['0903456789', '+84903456789'])->andReturn($order);

        $this->assertSame($order, (new TrackOrderAction($orders))->execute(5, '#LH-0042', '0903456789'));
    }

    public function test_country_code_input_is_normalised_too(): void
    {
        $orders = Mockery::mock(OrderRepositoryInterface::class);
        $orders->expects('findForTracking')->with(5, '#LH-0042', ['0903456789', '+84903456789'])->andReturnNull();

        $this->assertNull((new TrackOrderAction($orders))->execute(5, '#LH-0042', '+84903456789'));
    }
}
