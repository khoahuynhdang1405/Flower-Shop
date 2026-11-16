<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\CartItemType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderDeliveryDetail;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FloristSchedulePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        Carbon::setTestNow('2026-11-16 08:00:00');
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        auth('admin')->logout();

        $this->get(route('admin.orders.schedule'))->assertRedirect(route('admin.login'));
    }

    public function test_orders_page_links_to_the_schedule(): void
    {
        $this->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Xuất lịch giao hôm nay')
            ->assertSee(route('admin.orders.schedule'));
    }

    public function test_groups_todays_orders_by_time_slot(): void
    {
        $this->order('#LH-0001', '14:00-16:00', flower: 'Nắng trong veo');
        $this->order('#LH-0002', '08:00-10:00', flower: 'Lời thì thầm', card: 'Chúc mẹ luôn vui.');
        $this->order('#LH-0003', '14:00-16:00', status: OrderStatus::PreparedReady);

        $this->get(route('admin.orders.schedule'))
            ->assertOk()
            ->assertSee('Lịch cắm hoa')
            ->assertSee('16/11/2026 · 3 bó hoa · 1 bó đã cắm xong')
            ->assertSeeInOrder(['08:00 – 10:00', '#LH-0002', 'Lời thì thầm', 'Chúc mẹ luôn vui.', '10:00 – 12:00', 'Khung giờ này chưa có bó nào.', '14:00 – 16:00', '#LH-0001', '#LH-0003'])
            ->assertSee('1/2 bó đã xong');
    }

    public function test_shows_only_orders_the_florist_has_to_make(): void
    {
        $this->order('#LH-0001', payment: PaymentStatus::Pending, method: PaymentMethod::Cod);
        $this->order('#LH-0002', payment: PaymentStatus::Paid, method: PaymentMethod::Vnpay);
        $this->order('#LH-0003', payment: PaymentStatus::Pending, method: PaymentMethod::Vnpay);
        $this->order('#LH-0004', payment: PaymentStatus::Failed, method: PaymentMethod::Momo);
        $this->order('#LH-0005', status: OrderStatus::Cancelled);
        $this->order('#LH-0006', date: '2026-11-17');

        $this->get(route('admin.orders.schedule'))
            ->assertSee('#LH-0001')
            ->assertSee('#LH-0002')
            ->assertDontSee('#LH-0003')
            ->assertDontSee('#LH-0004')
            ->assertDontSee('#LH-0005')
            ->assertDontSee('#LH-0006');
    }

    public function test_shows_the_chosen_date(): void
    {
        $this->order('#LH-0001');
        $this->order('#LH-0002', date: '2026-11-17');

        $this->get(route('admin.orders.schedule', ['date' => '2026-11-17']))
            ->assertSee('#LH-0002')
            ->assertDontSee('#LH-0001');
    }

    public function test_shows_a_friendly_message_for_a_day_without_orders(): void
    {
        $this->get(route('admin.orders.schedule'))
            ->assertOk()
            ->assertSee('Ngày này tiệm chưa có bó nào cần cắm');
    }

    public function test_rejects_malformed_date(): void
    {
        $this->get(route('admin.orders.schedule', ['date' => '16/11/2026']))->assertSessionHasErrors('date');
    }

    private function order(
        string $code,
        string $slot = '14:00-16:00',
        OrderStatus $status = OrderStatus::Received,
        PaymentStatus $payment = PaymentStatus::Pending,
        PaymentMethod $method = PaymentMethod::Cod,
        string $date = '2026-11-16',
        string $flower = 'Bình yên xanh',
        ?string $card = null,
    ): Order {
        $order = Order::factory()->create([
            'order_code' => $code,
            'order_status' => $status,
            'payment_status' => $payment,
            'payment_method' => $method,
        ]);
        OrderDeliveryDetail::factory()->for($order)->create(['delivery_date' => $date, 'time_slot' => $slot, 'card_message' => $card]);
        OrderItem::factory()->for($order)->create(['item_type' => CartItemType::Flower, 'item_name' => $flower]);

        return $order;
    }
}
