<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Addon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Promotion;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Eloquent\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

/**
 * Đặt hoa từ trang thanh toán → trang xác nhận, chuyển sang VNPAY/MoMo và nhận kết quả về.
 */
class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        config([
            'services.vnpay.tmn_code' => 'TMNTEST',
            'services.vnpay.hash_secret' => 'VNPAYSECRET',
            'services.momo.partner_code' => 'MOMOTEST',
            'services.momo.access_key' => 'ACCESS',
            'services.momo.secret_key' => 'MOMOSECRET',
        ]);
        $this->actingAs(User::factory()->create());
    }

    public function test_guest_cannot_place_an_order(): void
    {
        $this->app['auth']->forgetGuards();
        $this->fillCartFromTheDesign();

        $this->post(route('checkout.store'), $this->form())->assertRedirect(route('login'));

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cod_order_is_saved_with_snapshot_items_and_the_cart_is_emptied(): void
    {
        $this->fillCartFromTheDesign();

        $response = $this->post(route('checkout.store'), $this->form());

        $order = Order::sole();
        $response->assertRedirect(route('orders.confirmation', $order));
        $this->assertMatchesRegularExpression('/^#LH-\d{4}$/', $order->order_code);
        $this->assertSame([800000, 40000, 0, 760000], [$order->subtotal, $order->discount_amount, $order->shipping_fee, $order->total_amount]);
        $this->assertSame('LAMOI', $order->promotion->code);
        $this->assertSame(PaymentMethod::Cod, $order->payment_method);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'item_type' => 'flower', 'item_name' => 'Lời thì thầm', 'size_name' => 'Vừa', 'unit_price' => 680000, 'total_price' => 680000]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'item_type' => 'addon', 'item_name' => 'Chocolate', 'total_price' => 120000]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'item_type' => 'addon', 'item_name' => 'Thiệp viết tay', 'unit_price' => 0, 'total_price' => 0]);
        $this->assertDatabaseHas('order_delivery_details', ['order_id' => $order->id, 'receiver_name' => 'Nguyễn Ngọc Đoan Trang', 'receiver_phone' => '0912345678', 'card_message' => 'Chúc Trang một ngày thật dịu dàng.']);
        $this->assertDatabaseHas('order_status_logs', ['order_id' => $order->id, 'status' => 'received', 'title' => 'Đơn hoa đã được tiếp nhận', 'description' => 'Lá đã nhận đơn của bạn và sẽ bắt đầu chọn hoa sớm thôi.', 'admin_id' => null]);

        $this->get(route('checkout'))->assertRedirect(route('cart.show'));
    }

    public function test_order_codes_follow_the_order_in_which_orders_were_placed(): void
    {
        $this->fillCartFromTheDesign();
        $this->post(route('checkout.store'), $this->form());
        $this->post(route('cart.flowers.store'), ['product_id' => Product::sole()->id, 'size' => ProductSize::sole()->id, 'quantity' => 1]);
        $this->post(route('checkout.store'), $this->form());

        [$first, $second] = Order::query()->orderBy('id')->get()->all();
        $this->assertSame('#LH-'.str_pad((string) $first->id, 4, '0', STR_PAD_LEFT), $first->order_code);
        $this->assertSame('#LH-'.str_pad((string) $second->id, 4, '0', STR_PAD_LEFT), $second->order_code);
        $this->assertLessThan($second->order_code, $first->order_code);
    }

    public function test_order_is_not_saved_when_writing_its_items_fails(): void
    {
        $this->fillCartFromTheDesign();
        $this->app->instance(OrderRepositoryInterface::class, $this->repositoryFailingOnItems());

        $this->post(route('checkout.store'), $this->form())->assertServerError();

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_delivery_details', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('order_status_logs', 0);
        $this->get(route('checkout'))->assertOk();
    }

    public function test_confirmation_page_shows_the_order_like_the_design(): void
    {
        $this->fillCartFromTheDesign();
        $this->post(route('checkout.store'), $this->form());
        $order = Order::sole();

        $this->get(route('orders.confirmation', $order))
            ->assertOk()
            ->assertSeeTextInOrder([
                'Tiệm đã nhận lời thương của bạn',
                'Cảm ơn Đăng Khoa',
                'Đặt hoa thành công · '.$order->order_code,
                'Thanh toán khi nhận hoa',
                'Hẹn giao một bó hoa xinh',
                '14:00–16:00 · Giao tận tay người nhận',
                'Nguyễn Ngọc Đoan Trang · 0912345678',
                '18 Võ Văn Tần, Phường 6, Quận 3, TP. Hồ Chí Minh',
                'Huỳnh Đăng Khoa',
                'Ghi chú: Gọi người nhận trước khi đến 15 phút.',
                'Tấm thiệp dành riêng cho Trang',
                'Chúc Trang một ngày thật dịu dàng.',
                'Người gửi: Đăng Khoa · Người nhận: Đoan Trang',
                'Đơn hoa của bạn',
                'Lời thì thầm',
                '680.000 VNĐ',
                'Ưu đãi LAMOI',
                '−40.000 VNĐ',
                'Giao hoa · Quận 3, TP. Hồ Chí Minh',
                '760.000 VNĐ',
            ])
            ->assertDontSee(route('orders.payment', $order));
    }

    public function test_self_pickup_uses_the_sender_as_receiver_and_drops_the_card(): void
    {
        $this->fillCartFromTheDesign();

        $this->post(route('checkout.store'), $this->form([
            'is_gift' => '0',
            'receiver_name' => '',
            'receiver_phone' => '',
            'card_message' => 'Không gửi',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('order_delivery_details', [
            'is_gift' => false,
            'receiver_name' => 'Huỳnh Đăng Khoa',
            'receiver_phone' => '0903456789',
            'card_message' => null,
            'card_sender' => null,
        ]);
    }

    public function test_signed_in_customer_owns_the_order(): void
    {
        $user = User::factory()->create();
        $this->fillCartFromTheDesign();

        $this->actingAs($user)->post(route('checkout.store'), $this->form());

        $this->assertSame($user->id, Order::sole()->user_id);
    }

    public function test_invalid_form_is_rejected_and_nothing_is_saved(): void
    {
        $this->fillCartFromTheDesign();

        $this->from(route('checkout'))->post(route('checkout.store'), $this->form([
            'sender_phone' => '123',
            'shipping_district' => 'Hà Nội',
            'delivery_date' => now()->subDay()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]))->assertRedirect(route('checkout'))
            ->assertSessionHasErrors(['sender_phone', 'shipping_district', 'delivery_date', 'payment_method']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_time_slot_that_already_started_today_is_rejected(): void
    {
        Carbon::setTestNow(today()->setTime(15, 0));
        $this->fillCartFromTheDesign();

        $this->post(route('checkout.store'), $this->form(['delivery_date' => today()->toDateString(), 'time_slot' => '14:00-16:00']))
            ->assertSessionHasErrors(['time_slot' => 'Khung giờ này của hôm nay đã bắt đầu, bạn chọn khung giờ khác giúp tiệm nhé.']);

        $this->post(route('checkout.store'), $this->form(['delivery_date' => today()->toDateString(), 'time_slot' => '16:00-18:00']))
            ->assertSessionHasNoErrors();
    }

    public function test_empty_cart_cannot_place_an_order(): void
    {
        $this->post(route('checkout.store'), $this->form())->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_another_browser_cannot_open_the_confirmation_page(): void
    {
        $order = Order::factory()->withDeliveryDetail()->create();

        $this->get(route('orders.confirmation', $order))->assertNotFound();
        $this->post(route('orders.payment', $order))->assertNotFound();
    }

    public function test_vnpay_order_redirects_to_the_signed_vnpay_payment_page(): void
    {
        $this->fillCartFromTheDesign();

        $response = $this->post(route('checkout.store'), $this->form(['payment_method' => 'vnpay']));

        $order = Order::sole();
        $location = (string) $response->headers->get('Location');
        $this->assertStringStartsWith('https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?', $location);
        parse_str((string) parse_url($location, PHP_URL_QUERY), $params);
        $this->assertSame('76000000', $params['vnp_Amount']);
        $this->assertArrayNotHasKey('vnp_BankCode', $params);
        $this->assertMatchesRegularExpression('/^LH'.$order->id.'T\d+$/', $params['vnp_TxnRef']);
        $this->assertSame(route('payment.return', 'vnpay'), $params['vnp_ReturnUrl']);
    }

    public function test_vnpay_return_marks_the_order_paid_and_shows_the_confirmation(): void
    {
        $order = $this->placeOnlineOrder(PaymentMethod::Vnpay);

        $this->get(route('payment.return', ['method' => 'vnpay', ...$this->vnpayResult($order)]))
            ->assertRedirect(route('orders.confirmation', $order));

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame('14226112', $order->transaction_ref);
        $this->get(route('orders.confirmation', $order))->assertSeeText('Đã thanh toán qua VNPAY');
    }

    public function test_cancelled_vnpay_payment_lets_the_customer_pay_again(): void
    {
        $order = $this->placeOnlineOrder(PaymentMethod::Vnpay);

        $this->get(route('payment.return', ['method' => 'vnpay', ...$this->vnpayResult($order, ['vnp_ResponseCode' => '24', 'vnp_TransactionStatus' => '02'])]));

        $this->assertSame(PaymentStatus::Failed, $order->refresh()->payment_status);
        $this->get(route('orders.confirmation', $order))
            ->assertSeeText('Thanh toán chưa thành công')
            ->assertSee(route('orders.payment', $order));
        $this->assertStringStartsWith('https://sandbox.vnpayment.vn/', (string) $this->post(route('orders.payment', $order))->headers->get('Location'));
    }

    public function test_vnpay_ipn_confirms_once_and_rejects_bad_data(): void
    {
        $order = $this->placeOnlineOrder(PaymentMethod::Vnpay);
        $result = $this->vnpayResult($order);

        $this->get(route('payment.ipn', ['method' => 'vnpay', ...$result, 'vnp_SecureHash' => str_repeat('0', 128)]))->assertExactJson(['RspCode' => '97', 'Message' => 'Invalid signature']);
        $this->get(route('payment.ipn', ['method' => 'vnpay', ...$this->vnpayResult($order, ['vnp_Amount' => '100'])]))->assertJson(['RspCode' => '04']);
        $this->assertSame(PaymentStatus::Pending, $order->refresh()->payment_status);

        $this->get(route('payment.ipn', ['method' => 'vnpay', ...$result]))->assertExactJson(['RspCode' => '00', 'Message' => 'Confirm Success']);
        $this->get(route('payment.ipn', ['method' => 'vnpay', ...$result]))->assertJson(['RspCode' => '02']);
        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
    }

    public function test_momo_order_redirects_to_the_momo_pay_url_and_ipn_marks_it_paid(): void
    {
        Http::fake(['test-payment.momo.vn/*' => Http::response(['resultCode' => 0, 'payUrl' => 'https://test-payment.momo.vn/pay/abc'])]);
        $this->fillCartFromTheDesign();

        $this->post(route('checkout.store'), $this->form(['payment_method' => 'momo']))
            ->assertRedirect('https://test-payment.momo.vn/pay/abc');

        $order = Order::sole();
        $this->postJson(route('payment.ipn', 'momo'), $this->momoResult($order))->assertNoContent();
        $this->assertSame(PaymentStatus::Paid, $order->refresh()->payment_status);
        $this->assertSame('4088878653', $order->transaction_ref);
    }

    public function test_momo_outage_keeps_the_order_and_offers_to_pay_again(): void
    {
        Http::fake(['test-payment.momo.vn/*' => Http::response(['resultCode' => 99, 'message' => 'Unknown error'], 500)]);
        $this->fillCartFromTheDesign();

        $response = $this->post(route('checkout.store'), $this->form(['payment_method' => 'momo']));

        $order = Order::sole();
        $response->assertRedirect(route('orders.confirmation', $order))->assertSessionHas('payment_error', true);
        $this->get(route('orders.confirmation', $order))
            ->assertSeeText('Cổng MoMo đang bận một chút')
            ->assertSee(route('orders.payment', $order));
    }

    private function fillCartFromTheDesign(): void
    {
        $whisper = Product::factory()->create(['name' => 'Lời thì thầm', 'slug' => 'loi-thi-tham', 'base_price' => 480000]);
        $medium = ProductSize::factory()->for($whisper)->create(['size_name' => 'Vừa', 'price' => 680000, 'is_default' => true]);
        $chocolate = Addon::factory()->create(['name' => 'Chocolate', 'price' => 120000]);
        $card = Addon::factory()->free()->create(['name' => 'Thiệp viết tay']);
        Promotion::factory()->create(['code' => 'LAMOI', 'discount_value' => 40000, 'min_order_amount' => 0]);

        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1, 'addons' => [$chocolate->id, $card->id]]);
        $this->post(route('cart.promotion.store'), ['code' => 'LAMOI']);
    }

    /**
     * Repository thật, riêng bước lưu các món thì lỗi (đơn và lịch giao đã ghi trước đó trong transaction).
     */
    private function repositoryFailingOnItems(): OrderRepositoryInterface
    {
        return new class(new OrderRepository) implements OrderRepositoryInterface
        {
            public function __construct(private OrderRepositoryInterface $orders) {}

            public function create(array $attributes): Order
            {
                return $this->orders->create($attributes);
            }

            public function assignOrderCode(Order $order, string $orderCode): void
            {
                $this->orders->assignOrderCode($order, $orderCode);
            }

            public function createDeliveryDetail(Order $order, array $attributes): void
            {
                $this->orders->createDeliveryDetail($order, $attributes);
            }

            public function createItems(Order $order, array $items): void
            {
                throw new RuntimeException('Writing order items failed.');
            }

            public function createStatusLog(Order $order, array $attributes): void
            {
                $this->orders->createStatusLog($order, $attributes);
            }

            public function findById(int $id): ?Order
            {
                return $this->orders->findById($id);
            }

            public function findWithDetailsById(int $id): ?Order
            {
                return $this->orders->findWithDetailsById($id);
            }

            public function markPaymentPaid(Order $order, ?string $transactionRef): bool
            {
                return $this->orders->markPaymentPaid($order, $transactionRef);
            }

            public function markPaymentFailed(Order $order): void
            {
                $this->orders->markPaymentFailed($order);
            }
        };
    }

    private function placeOnlineOrder(PaymentMethod $method): Order
    {
        $this->fillCartFromTheDesign();
        $this->post(route('checkout.store'), $this->form(['payment_method' => $method->value]));

        return Order::sole();
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
            'is_gift' => '1',
            'receiver_name' => 'Nguyễn Ngọc Đoan Trang',
            'receiver_phone' => '0912 345 678',
            'shipping_address' => '18 Võ Văn Tần, Phường 6',
            'shipping_district' => 'Quận 3',
            'delivery_notes' => 'Gọi người nhận trước khi đến 15 phút.',
            'delivery_date' => today()->addDay()->toDateString(),
            'time_slot' => '14:00-16:00',
            'card_sender' => 'Đăng Khoa',
            'card_receiver' => 'Đoan Trang',
            'card_message' => 'Chúc Trang một ngày thật dịu dàng.',
            'payment_method' => 'cod',
            ...$overrides,
        ];
    }

    /**
     * Tham số VNPAY gửi về (trang quay về / IPN) cho đơn, đã ký bằng hash secret thử.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function vnpayResult(Order $order, array $overrides = []): array
    {
        $params = [
            'vnp_Amount' => (string) ($order->total_amount * 100),
            'vnp_BankCode' => 'VNPAYQR',
            'vnp_OrderInfo' => 'Thanh toan don hoa',
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

    /**
     * IPN MoMo gửi về cho đơn, đã ký bằng secret key thử.
     *
     * @return array<string, int|string>
     */
    private function momoResult(Order $order): array
    {
        $params = [
            'amount' => $order->total_amount,
            'extraData' => '',
            'message' => 'Successful.',
            'orderId' => 'LH'.$order->id.'T1700000000',
            'orderInfo' => 'Thanh toan don hoa',
            'orderType' => 'momo_wallet',
            'partnerCode' => 'MOMOTEST',
            'payType' => 'qr',
            'requestId' => 'LH'.$order->id.'T1700000000',
            'responseTime' => 1731234567890,
            'resultCode' => 0,
            'transId' => 4088878653,
        ];
        $data = 'accessKey=ACCESS&'.implode('&', array_map(fn (string $key, int|string $value): string => $key.'='.$value, array_keys($params), $params));

        return [...$params, 'signature' => hash_hmac('sha256', $data, 'MOMOSECRET')];
    }
}
