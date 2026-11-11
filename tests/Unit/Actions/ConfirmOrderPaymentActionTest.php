<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Order\ConfirmOrderPaymentAction;
use App\Actions\Order\SendOrderConfirmationAction;
use App\DTOs\Payment\PaymentResultData;
use App\Enums\PaymentConfirmationOutcome;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\SendOrderConfirmationEmailJob;
use App\Models\Order;
use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\PaymentGatewayResolver;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\Bus;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Ghi kết quả thanh toán cổng gửi về, với cổng và repository giả.
 */
class ConfirmOrderPaymentActionTest extends TestCase
{
    private PaymentGatewayInterface&MockInterface $gateway;

    private OrderRepositoryInterface&MockInterface $orders;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = Mockery::mock(PaymentGatewayInterface::class);
        $this->orders = Mockery::mock(OrderRepositoryInterface::class);
        Bus::fake();
    }

    public function test_successful_payment_marks_the_order_paid(): void
    {
        $order = $this->order();
        $this->gatewayReturns(new PaymentResultData(true, true, 'LH12T1700000000', 760000, '14226112'));
        $this->orders->expects('findById')->with(12)->andReturn($order);
        $this->orders->expects('markPaymentPaid')->with($order, '14226112')->andReturn(true);

        $confirmation = $this->action()->execute(PaymentMethod::Vnpay, []);

        $this->assertSame(PaymentConfirmationOutcome::Confirmed, $confirmation->outcome);
        $this->assertSame($order, $confirmation->order);
        Bus::assertDispatchedTimes(SendOrderConfirmationEmailJob::class, 1);
        Bus::assertDispatched(SendOrderConfirmationEmailJob::class, fn (SendOrderConfirmationEmailJob $job): bool => $job->orderId === 12);
    }

    public function test_payment_already_recorded_by_a_concurrent_request_sends_no_second_email(): void
    {
        $order = $this->order();
        $this->gatewayReturns(new PaymentResultData(true, true, 'LH12T1700000000', 760000, '14226112'));
        $this->orders->expects('findById')->andReturn($order);
        $this->orders->expects('markPaymentPaid')->andReturn(false);

        $this->assertSame(PaymentConfirmationOutcome::Confirmed, $this->action()->execute(PaymentMethod::Vnpay, [])->outcome);
        Bus::assertNotDispatched(SendOrderConfirmationEmailJob::class);
    }

    public function test_unsuccessful_payment_marks_a_pending_order_failed(): void
    {
        $order = $this->order();
        $this->gatewayReturns(new PaymentResultData(true, false, 'LH12T1700000000', 760000, null));
        $this->orders->expects('findById')->andReturn($order);
        $this->orders->expects('markPaymentFailed')->with($order);

        $this->assertSame(PaymentConfirmationOutcome::Confirmed, $this->action()->execute(PaymentMethod::Vnpay, [])->outcome);
        Bus::assertNotDispatched(SendOrderConfirmationEmailJob::class);
    }

    public function test_a_paid_order_is_never_changed_again(): void
    {
        $this->gatewayReturns(new PaymentResultData(true, false, 'LH12T1700000000', 760000, null));
        $this->orders->expects('findById')->andReturn($this->order(PaymentStatus::Paid));
        $this->orders->shouldNotReceive('markPaymentFailed', 'markPaymentPaid');

        $this->assertSame(PaymentConfirmationOutcome::AlreadyConfirmed, $this->action()->execute(PaymentMethod::Vnpay, [])->outcome);
    }

    public function test_a_failed_order_can_still_be_paid_by_a_later_attempt(): void
    {
        $order = $this->order(PaymentStatus::Failed);
        $this->gatewayReturns(new PaymentResultData(true, true, 'LH12T1700000099', 760000, '999'));
        $this->orders->expects('findById')->andReturn($order);
        $this->orders->expects('markPaymentPaid')->with($order, '999')->andReturn(true);

        $this->assertSame(PaymentConfirmationOutcome::Confirmed, $this->action()->execute(PaymentMethod::Vnpay, [])->outcome);
    }

    public function test_invalid_signature_touches_nothing(): void
    {
        $this->gatewayReturns(PaymentResultData::invalidSignature());
        $this->orders->shouldNotReceive('findById');

        $this->assertSame(PaymentConfirmationOutcome::InvalidSignature, $this->action()->execute(PaymentMethod::Vnpay, [])->outcome);
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        $this->gatewayReturns(new PaymentResultData(true, true, 'LH12T1700000000', 1000, '1'));
        $this->orders->expects('findById')->andReturn($this->order());
        $this->orders->shouldNotReceive('markPaymentPaid');

        $this->assertSame(PaymentConfirmationOutcome::InvalidAmount, $this->action()->execute(PaymentMethod::Vnpay, [])->outcome);
    }

    public function test_unknown_reference_is_not_found(): void
    {
        $this->gatewayReturns(new PaymentResultData(true, true, 'SOMETHING-ELSE', 760000, '1'));
        $this->orders->shouldNotReceive('findById');

        $this->assertSame(PaymentConfirmationOutcome::OrderNotFound, $this->action()->execute(PaymentMethod::Vnpay, [])->outcome);
    }

    public function test_order_paid_with_another_method_is_not_found(): void
    {
        $this->gatewayReturns(new PaymentResultData(true, true, 'LH12T1700000000', 760000, '1'));
        $this->orders->expects('findById')->andReturn($this->order(method: PaymentMethod::Momo));

        $this->assertSame(PaymentConfirmationOutcome::OrderNotFound, $this->action()->execute(PaymentMethod::Vnpay, [])->outcome);
    }

    private function action(): ConfirmOrderPaymentAction
    {
        return new ConfirmOrderPaymentAction(
            new PaymentGatewayResolver([PaymentMethod::Vnpay->value => $this->gateway]),
            $this->orders,
            new SendOrderConfirmationAction(Bus::getFacadeRoot()),
        );
    }

    private function gatewayReturns(PaymentResultData $result): void
    {
        $this->gateway->allows('verify')->andReturn($result);
    }

    private function order(PaymentStatus $status = PaymentStatus::Pending, PaymentMethod $method = PaymentMethod::Vnpay): Order
    {
        return (new Order)->forceFill([
            'id' => 12,
            'total_amount' => 760000,
            'payment_method' => $method,
            'payment_status' => $status,
        ]);
    }
}
