<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\CartItemType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderDeliveryDetail;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPageTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        $this->admin = Admin::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        auth('admin')->logout();

        $this->get(route('admin.orders'))->assertRedirect(route('admin.login'));
    }

    public function test_lists_orders_with_tab_counts(): void
    {
        $this->order('#LH-0001', OrderStatus::Received, sender: 'Nguyễn Minh Anh', flower: 'Lời thì thầm');
        $this->order('#LH-0002', OrderStatus::Preparing);
        $this->order('#LH-0003', OrderStatus::PreparedReady);
        $this->order('#LH-0004', OrderStatus::Shipping);
        $this->order('#LH-0005', OrderStatus::Cancelled);

        $this->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Quản lý đơn hàng')
            ->assertSee('#LH-0001')
            ->assertSee('Nguyễn Minh Anh')
            ->assertSee('Lời thì thầm')
            ->assertSee('Tất cả (5)')
            ->assertSee('Chờ xác nhận (1)')
            ->assertSee('Đang chuẩn bị (2)')
            ->assertSee('Đang giao (1)')
            ->assertSee('Hoàn thành (0)')
            ->assertSee('Đã hủy (1)');
    }

    public function test_preparing_tab_includes_orders_ready_to_deliver(): void
    {
        $this->order('#LH-0001', OrderStatus::Received);
        $this->order('#LH-0002', OrderStatus::Preparing);
        $this->order('#LH-0003', OrderStatus::PreparedReady);

        $this->get(route('admin.orders', ['status' => 'preparing']))
            ->assertOk()
            ->assertSee('#LH-0002')
            ->assertSee('#LH-0003')
            ->assertSee('Sẵn sàng giao')
            ->assertDontSee('#LH-0001');
    }

    public function test_search_matches_order_code_name_or_phone(): void
    {
        $this->order('#LH-0001', OrderStatus::Received, sender: 'Trần Hoàng Nam', phone: '0911111111');
        $this->order('#LH-0002', OrderStatus::Received, sender: 'Phạm Thu Hà', phone: '0922222222');

        $this->get(route('admin.orders', ['q' => 'hoàng']))->assertSee('#LH-0001')->assertDontSee('#LH-0002');
        $this->get(route('admin.orders', ['q' => '0922222']))->assertSee('#LH-0002')->assertDontSee('#LH-0001');
        $this->get(route('admin.orders', ['q' => 'LH-0002']))->assertSee('Phạm Thu Hà')->assertDontSee('Trần Hoàng Nam');
    }

    public function test_filters_by_payment_status_and_keeps_it_in_tab_counts(): void
    {
        $this->order('#LH-0001', OrderStatus::Received, payment: PaymentStatus::Paid);
        $this->order('#LH-0002', OrderStatus::Received, payment: PaymentStatus::Pending);

        $this->get(route('admin.orders', ['payment' => 'paid']))
            ->assertSee('#LH-0001')
            ->assertDontSee('#LH-0002')
            ->assertSee('Tất cả (1)');
    }

    public function test_shows_a_friendly_message_when_nothing_matches(): void
    {
        $this->order('#LH-0001', OrderStatus::Received);

        $this->get(route('admin.orders', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Không có đơn nào khớp với bộ lọc');
    }

    public function test_rejects_unknown_tab(): void
    {
        $this->get(route('admin.orders', ['status' => 'prepared_ready']))->assertSessionHasErrors('status');
    }

    public function test_advances_order_and_logs_the_admin(): void
    {
        $order = $this->order('#LH-0001', OrderStatus::Received);

        $this->from(route('admin.orders'))
            ->post(route('admin.orders.advance', $order))
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHas('status');

        $this->assertSame(OrderStatus::Preparing, $order->refresh()->order_status);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'admin_id' => $this->admin->id,
            'status' => OrderStatus::Preparing->value,
        ]);
    }

    public function test_completed_order_is_not_advanced(): void
    {
        $order = $this->order('#LH-0001', OrderStatus::Completed);

        $this->from(route('admin.orders'))
            ->post(route('admin.orders.advance', $order))
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::Completed, $order->refresh()->order_status);
        $this->assertDatabaseCount('order_status_logs', 0);
    }

    private function order(
        string $code,
        OrderStatus $status,
        PaymentStatus $payment = PaymentStatus::Pending,
        string $sender = 'Huỳnh Đăng Khoa',
        string $phone = '0903456789',
        string $flower = 'Nắng trong veo',
    ): Order {
        $order = Order::factory()->create(['order_code' => $code, 'order_status' => $status, 'payment_status' => $payment]);
        OrderDeliveryDetail::factory()->for($order)->create(['sender_name' => $sender, 'sender_phone' => $phone]);
        OrderItem::factory()->for($order)->create(['item_type' => CartItemType::Flower, 'item_name' => $flower]);

        return $order;
    }
}
