<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CartItemType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderDeliveryDetail;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Trang "Bó hoa đang đến đâu?": khách đăng nhập tra đơn của mình bằng mã đơn + SĐT người đặt.
 */
class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        $this->user = User::factory()->create();
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get(route('orders.track'))->assertRedirect(route('login'));
    }

    public function test_shows_only_the_form_without_a_lookup(): void
    {
        $this->actingAs($this->user)->get(route('orders.track'))
            ->assertOk()
            ->assertSee('Bó hoa đang đến đâu?')
            ->assertSee('Mã đơn hàng')
            ->assertDontSee('Hành trình đơn hoa')
            ->assertDontSee('Lá chưa tìm thấy đơn');
    }

    public function test_shows_the_order_journey_like_the_design(): void
    {
        $order = $this->order(OrderStatus::Preparing);
        OrderStatusLog::factory()->for($order)->create(['status' => OrderStatus::Received, 'title' => 'Đơn hoa đã được tiếp nhận', 'created_at' => now()->subHours(3)]);
        OrderStatusLog::factory()->for($order)->create(['status' => OrderStatus::Preparing, 'title' => 'Tiệm đang cắm hoa cho bạn', 'created_at' => now()->subHour()]);

        $this->actingAs($this->user)->get(route('orders.track', ['code' => '#LH-0042', 'phone' => '0903456789']))
            ->assertOk()
            ->assertSee('Hành trình đơn hoa #LH-0042')
            ->assertSee('Đang cắm hoa')
            ->assertSee('Tiệm đang chọn những cành hoa đẹp nhất cho Đoan Trang.')
            ->assertSee('Chờ giao hoa')
            ->assertSee('Lịch hẹn giao hoa')
            ->assertSee('14:00–16:00')
            ->assertSee('Những cập nhật từ tiệm')
            ->assertSee('Đơn hoa đã được tiếp nhận')
            ->assertSee('Món quà bạn đã gửi')
            ->assertSee('Lời thì thầm')
            ->assertSee('Kèm Chocolate 6 viên')
            ->assertSee('Chúc Trang một ngày thật dịu dàng.')
            ->assertSee('tel:'.config('shop.hotline'), false);
    }

    public function test_accepts_loosely_typed_code_and_phone(): void
    {
        $this->order(OrderStatus::Received);

        $this->actingAs($this->user)->get(route('orders.track', ['code' => ' lh42 ', 'phone' => '0903 456 789']))
            ->assertOk()
            ->assertSee('Hành trình đơn hoa #LH-0042');
    }

    public function test_matches_phone_written_with_country_code(): void
    {
        $this->order(OrderStatus::Received, phone: '+84903456789');

        $this->actingAs($this->user)->get(route('orders.track', ['code' => '#LH-0042', 'phone' => '0903456789']))
            ->assertSee('Hành trình đơn hoa #LH-0042');
    }

    public function test_wrong_phone_finds_nothing(): void
    {
        $this->order(OrderStatus::Received);

        $this->actingAs($this->user)->get(route('orders.track', ['code' => '#LH-0042', 'phone' => '0911111111']))
            ->assertOk()
            ->assertSee('Lá chưa tìm thấy đơn')
            ->assertDontSee('Hành trình đơn hoa');
    }

    public function test_another_customers_order_is_not_shown(): void
    {
        $this->order(OrderStatus::Received, owner: User::factory()->create());

        $this->actingAs($this->user)->get(route('orders.track', ['code' => '#LH-0042', 'phone' => '0903456789']))
            ->assertSee('Lá chưa tìm thấy đơn')
            ->assertDontSee('Đoan Trang');
    }

    public function test_ready_to_deliver_order_sits_on_the_arranging_step(): void
    {
        $this->order(OrderStatus::PreparedReady);

        $this->actingAs($this->user)->get(route('orders.track', ['code' => '#LH-0042', 'phone' => '0903456789']))
            ->assertSee('đã cắm xong, đang chờ người giao')
            ->assertSee('aria-current="step"', false)
            ->assertSee('Chờ giao hoa');
    }

    public function test_cancelled_order_shows_a_message_instead_of_the_timeline(): void
    {
        $this->order(OrderStatus::Cancelled);

        $this->actingAs($this->user)->get(route('orders.track', ['code' => '#LH-0042', 'phone' => '0903456789']))
            ->assertSee('Đơn hoa này đã được hủy')
            ->assertDontSee('aria-current="step"', false);
    }

    public function test_invalid_phone_returns_to_the_form_with_an_error(): void
    {
        $this->actingAs($this->user)->get(route('orders.track', ['code' => '#LH-0042', 'phone' => '123']))
            ->assertRedirect(route('orders.track'))
            ->assertSessionHasErrors('phone');
    }

    private function order(OrderStatus $status, string $phone = '0903456789', ?User $owner = null): Order
    {
        $order = Order::factory()->create([
            'user_id' => ($owner ?? $this->user)->id,
            'order_code' => '#LH-0042',
            'order_status' => $status,
        ]);
        OrderDeliveryDetail::factory()->for($order)->create(['sender_phone' => $phone]);
        OrderItem::factory()->for($order)->create();
        OrderItem::factory()->for($order)->create(['item_type' => CartItemType::Addon, 'item_name' => 'Chocolate 6 viên', 'size_name' => null]);

        return $order;
    }
}
