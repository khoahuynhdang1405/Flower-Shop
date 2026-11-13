<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Jobs\SendOrderConfirmationEmailJob;
use App\Mail\OrderConfirmationMail;
use App\Models\Addon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Promotion;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Email xác nhận đơn: COD gửi khi đặt, VNPAY/MoMo gửi một lần khi đã thanh toán, gửi tới email người đặt.
 */
class OrderConfirmationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        config(['services.vnpay.tmn_code' => 'TMNTEST', 'services.vnpay.hash_secret' => 'VNPAYSECRET']);
        $this->actingAs(User::factory()->create());
    }

    public function test_cod_order_queues_the_confirmation_email(): void
    {
        Bus::fake([SendOrderConfirmationEmailJob::class]);
        $this->fillCart();

        $this->post(route('checkout.store'), $this->form());

        Bus::assertDispatchedTimes(SendOrderConfirmationEmailJob::class, 1);
        Bus::assertDispatched(SendOrderConfirmationEmailJob::class, fn (SendOrderConfirmationEmailJob $job): bool => $job->orderId === Order::sole()->id);
    }

    public function test_vnpay_order_is_emailed_once_only_after_it_is_paid(): void
    {
        Bus::fake([SendOrderConfirmationEmailJob::class]);
        $this->fillCart();

        $this->post(route('checkout.store'), $this->form(['payment_method' => 'vnpay']));
        Bus::assertNotDispatched(SendOrderConfirmationEmailJob::class);

        $order = Order::sole();
        $result = $this->vnpayResult($order);
        $this->get(route('payment.return', ['method' => 'vnpay', ...$result]));
        $this->get(route('payment.ipn', ['method' => 'vnpay', ...$result]));
        $this->get(route('payment.return', ['method' => 'vnpay', ...$result]));

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        Bus::assertDispatchedTimes(SendOrderConfirmationEmailJob::class, 1);
    }

    public function test_failed_vnpay_payment_sends_no_email(): void
    {
        Bus::fake([SendOrderConfirmationEmailJob::class]);
        $this->fillCart();
        $this->post(route('checkout.store'), $this->form(['payment_method' => 'vnpay']));

        $this->get(route('payment.return', ['method' => 'vnpay', ...$this->vnpayResult(Order::sole(), ['vnp_ResponseCode' => '24', 'vnp_TransactionStatus' => '02'])]));

        Bus::assertNotDispatched(SendOrderConfirmationEmailJob::class);
    }

    public function test_job_mails_the_sender_email_entered_at_checkout(): void
    {
        Mail::fake();
        $order = Order::factory()->withDeliveryDetail()->create();
        $order->deliveryDetail->update(['sender_email' => 'khoa@example.com']);

        (new SendOrderConfirmationEmailJob($order->id))->handle(app(OrderRepositoryInterface::class), app('mail.manager'));

        Mail::assertSent(OrderConfirmationMail::class, fn (OrderConfirmationMail $mail): bool => $mail->hasTo('khoa@example.com') && $mail->order->is($order));
    }

    public function test_job_for_a_missing_order_sends_nothing(): void
    {
        Mail::fake();

        (new SendOrderConfirmationEmailJob(999))->handle(app(OrderRepositoryInterface::class), app('mail.manager'));

        Mail::assertNothingSent();
    }

    public function test_email_shows_the_order_like_the_confirmation_page(): void
    {
        $order = Order::factory()->withDeliveryDetail()->create([
            'order_code' => '#LH-0042',
            'subtotal' => 800000,
            'discount_amount' => 40000,
            'total_amount' => 760000,
            'payment_method' => PaymentMethod::Vnpay,
            'payment_status' => PaymentStatus::Paid,
            'promotion_id' => Promotion::factory()->create(['code' => 'LAMOI'])->id,
        ]);
        $order->deliveryDetail->update([
            'sender_name' => 'Huỳnh Đăng Khoa',
            'is_gift' => true,
            'receiver_name' => 'Nguyễn Ngọc Đoan Trang',
            'delivery_date' => '2026-11-20',
            'time_slot' => '14:00-16:00',
            'card_message' => 'Chúc Trang một ngày thật dịu dàng.',
        ]);
        OrderItem::factory()->for($order)->create(['item_name' => 'Lời thì thầm', 'size_name' => 'Vừa', 'total_price' => 680000]);

        $mail = new OrderConfirmationMail($order->fresh(['deliveryDetail', 'items', 'promotion']));

        $mail->assertHasSubject('Lá & Hoa · Đặt hoa thành công #LH-0042');
        $mail->assertSeeInOrderInHtml([
            'Đặt hoa thành công · #LH-0042',
            'Chào Đăng Khoa,',
            'Đã thanh toán qua VNPAY',
            'Lời thì thầm · Vừa',
            '680.000 VNĐ',
            'Ưu đãi LAMOI',
            '−40.000 VNĐ',
            'Miễn phí',
            '760.000 VNĐ',
            '20/11/2026 · 14:00-16:00',
            'Nguyễn Ngọc Đoan Trang',
            'Chúc Trang một ngày thật dịu dàng.',
            config('shop.hotline'),
        ], false);
    }

    private function fillCart(): void
    {
        $whisper = Product::factory()->create(['name' => 'Lời thì thầm', 'base_price' => 480000]);
        $medium = ProductSize::factory()->for($whisper)->create(['size_name' => 'Vừa', 'price' => 680000, 'is_default' => true]);
        $chocolate = Addon::factory()->create(['name' => 'Chocolate', 'price' => 120000]);

        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1, 'addons' => [$chocolate->id]]);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function form(array $overrides = []): array
    {
        return [
            'sender_name' => 'Huỳnh Đăng Khoa',
            'sender_phone' => '0903 456 789',
            'sender_email' => 'khoa@example.com',
            'is_gift' => '0',
            'shipping_address' => '18 Võ Văn Tần, Phường 6',
            'shipping_district' => 'Quận 3',
            'delivery_date' => today()->addDay()->toDateString(),
            'time_slot' => '14:00-16:00',
            'payment_method' => 'cod',
            ...$overrides,
        ];
    }

    /**
     * Kết quả VNPAY gửi về cho đơn, đã ký bằng hash secret thử.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function vnpayResult(Order $order, array $overrides = []): array
    {
        $params = [
            'vnp_Amount' => (string) ($order->total_amount * 100),
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => 'TMNTEST',
            'vnp_TransactionNo' => '14226112',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => 'LH'.$order->id.'T1700000000',
            ...$overrides,
        ];
        ksort($params);

        return [...$params, 'vnp_SecureHash' => hash_hmac('sha512', http_build_query($params), 'VNPAYSECRET')];
    }
}
