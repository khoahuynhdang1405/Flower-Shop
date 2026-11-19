<?php

namespace Tests\Feature;

use App\Enums\CartItemType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->actingAs(Admin::factory()->create(['name' => 'Đăng Khoa']), 'admin');
    }

    public function test_dashboard_renders_layout_in_vietnamese(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 09:00:00'));
        app()->setLocale('vi');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Một ngày đẹp ở Lá & Hoa')
            ->assertSeeText('Thứ Tư, 07 tháng 10, 2026 · Cùng chăm chút những điều thương.')
            ->assertSeeText('Hôm nay, 07/10')
            ->assertSeeText('Không gian quản trị')
            ->assertSeeText('© 2026 Lá & Hoa · Không gian quản trị nội bộ')
            ->assertSee('<span aria-current="page">Tổng quan</span>', false);
    }

    public function test_dashboard_renders_in_english(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 09:00:00'));
        app()->setLocale('en');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('A lovely day at Lá & Hoa')
            ->assertSeeText('Wednesday, 7 October 2026')
            ->assertSeeText('Today, Oct 7')
            ->assertSee('<html lang="en"', false);
    }

    public function test_dashboard_menu_item_is_the_only_active_link(): void
    {
        $html = $this->get(route('admin.dashboard'))->getContent();

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.dashboard'), '/').'"\s+aria-current="page"/',
            $html,
        );
        // Một aria-current cho mục menu, một cho breadcrumb.
        $this->assertSame(2, substr_count($html, 'aria-current="page"'));
    }

    public function test_menu_items_without_routes_fall_back_to_placeholder_links(): void
    {
        app()->setLocale('vi');

        $this->get(route('admin.dashboard'))
            ->assertSeeText('Đơn hàng')
            ->assertSee('href="#"', false);
    }

    public function test_revenue_counts_only_completed_orders_on_their_completion_day(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 15:00:00'));
        app()->setLocale('vi');
        $this->completedOrder(12450000, '2026-10-07 10:00:00');
        $this->completedOrder(10000000, '2026-10-06 18:00:00');
        $this->completedOrder(5000000, '2026-10-01 09:00:00');
        $this->completedOrder(9999000, '2026-09-30 09:00:00'); // ngoài 7 ngày, thuộc kỳ trước
        Order::factory()->create(['order_status' => OrderStatus::Shipping, 'total_amount' => 777000]);
        $cancelled = $this->completedOrder(3000000, '2026-10-07 11:00:00');
        $cancelled->update(['order_status' => OrderStatus::Cancelled]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeTextInOrder(['Doanh thu', '12.450.000 VNĐ', '+24,5% so với hôm qua'])
            ->assertSeeTextInOrder(['Doanh thu 7 ngày qua', 'Đơn vị: triệu VNĐ', '27.450.000 VNĐ'])
            ->assertSeeText('Tăng 174,5% so với 7 ngày trước')
            ->assertSeeTextInOrder(['01/10/2026', '5.000.000 VNĐ', '1', '02/10/2026', '0 VNĐ', '0'])
            ->assertSeeTextInOrder(['07/10/2026 (hôm nay)', '12.450.000 VNĐ', '1'])
            ->assertSee('data-revenue-chart', false)
            ->assertSee('data-url="'.route('admin.dashboard.revenue').'"', false)
            ->assertDontSeeText('777.000 VNĐ');
    }

    public function test_revenue_api_returns_daily_revenue_and_orders_for_chart_js(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 15:00:00'));
        $this->completedOrder(12450000, '2026-10-07 10:00:00');
        $this->completedOrder(690000, '2026-10-07 11:00:00');
        $this->completedOrder(5000000, '2026-10-01 09:00:00');
        $this->completedOrder(9999000, '2026-09-30 09:00:00');

        $this->getJson(route('admin.dashboard.revenue', ['range' => 7]))
            ->assertOk()
            ->assertJsonPath('data.days', 7)
            ->assertJsonPath('data.labels', ['01/10', '02/10', '03/10', '04/10', '05/10', '06/10', '07/10'])
            ->assertJsonPath('data.revenue', [5000000, 0, 0, 0, 0, 0, 13140000])
            ->assertJsonPath('data.orders', [1, 0, 0, 0, 0, 0, 2])
            ->assertJsonPath('data.totals', ['revenue' => 18140000, 'orders' => 3])
            ->assertJsonPath('data.changes.orders', 200);
    }

    public function test_revenue_api_returns_thirty_days_and_compares_with_the_thirty_before(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 15:00:00'));
        $this->completedOrder(9999000, '2026-09-30 09:00:00');
        $this->completedOrder(1000000, '2026-09-01 09:00:00');

        $response = $this->getJson(route('admin.dashboard.revenue', ['range' => 30]))->assertOk();

        $this->assertCount(30, $response->json('data.labels'));
        $this->assertSame('08/09', $response->json('data.labels.0'));
        $response->assertJsonPath('data.totals.revenue', 9999000)->assertJsonPath('data.changes.revenue', 899.9);
    }

    public function test_revenue_api_rejects_unknown_ranges_and_guests(): void
    {
        $this->getJson(route('admin.dashboard.revenue', ['range' => 90]))->assertUnprocessable()->assertJsonValidationErrors('range');

        auth('admin')->logout();
        $this->getJson(route('admin.dashboard.revenue'))->assertUnauthorized();
    }

    public function test_cards_compare_orders_and_customers_with_yesterday(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 15:00:00'));
        app()->setLocale('vi');
        Order::factory()->count(3)->create();
        Order::factory()->create(['created_at' => now()->subDay()]);
        User::factory()->count(2)->create();
        User::factory()->count(4)->create(['created_at' => now()->subDay()]);
        Product::factory()->count(3)->create(['stock' => 20]);
        Product::factory()->create(['stock' => 5]);
        Product::factory()->create(['stock' => 0]);
        Product::factory()->hidden()->create(['stock' => 1]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeTextInOrder(['Đơn hàng mới', '03', '+2 đơn so với hôm qua'])
            ->assertSeeTextInOrder(['Khách hàng mới', '02', '−2 người bạn mới so với hôm qua'])
            ->assertSeeTextInOrder(['Sản phẩm đang bán', '05', '2 sản phẩm sắp hết'])
            ->assertSeeText('Doanh thu 7 ngày qua')
            ->assertSeeText('Đơn hoàn thành trong ngày');
    }

    public function test_todo_panel_links_to_waiting_orders_and_counts_morning_bouquets(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 07:00:00'));
        app()->setLocale('vi');
        Order::factory()->count(3)->create(['order_status' => OrderStatus::Received]);
        $this->orderForToday('08:00-10:00', quantity: 2);
        $this->orderForToday('10:00-12:00', quantity: 3);
        $this->orderForToday('14:00-16:00', quantity: 4);
        $this->orderForToday('08:00-10:00', quantity: 5, status: OrderStatus::Cancelled);
        $this->orderForToday('08:00-10:00', quantity: 6, method: PaymentMethod::Vnpay);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeTextInOrder(['Tiệm cần bạn một chút', '03', 'đơn đang chờ xác nhận', 'sản phẩm sắp hết hàng'])
            ->assertSee('href="'.route('admin.orders', ['status' => 'received']).'"', false)
            ->assertSeeText('5 bó hoa cần giao trước 12:00 hôm nay.')
            ->assertDontSeeText('lời nhắn cần phản hồi');
    }

    public function test_lists_five_latest_orders_and_featured_products(): void
    {
        app()->setLocale('vi');
        foreach (range(1, 6) as $number) {
            $order = Order::factory()->withDeliveryDetail()->create([
                'order_code' => '#LH-100'.$number,
                'created_at' => now()->subMinutes(10 - $number),
                'total_amount' => 690000,
            ]);
            OrderItem::factory()->for($order)->create(['item_name' => 'Hoa số '.$number]);
        }
        $category = Category::factory()->create(['name' => 'Bó hoa']);
        Product::factory()->featured()->for($category)->create(['name' => 'Nắng trong veo', 'base_price' => 550000, 'stock' => 18]);
        Product::factory()->featured()->for($category)->create(['name' => 'Thu dịu dàng', 'base_price' => 890000, 'stock' => 3]);
        Product::factory()->featured()->hidden()->create(['name' => 'Cúc họa mi tháng mười']);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeTextInOrder(['Đơn hàng gần đây', '#LH-1006', 'Hoa số 6', '690.000', '#LH-1002'])
            ->assertDontSeeText('#LH-1001')
            ->assertSeeTextInOrder(['Quản lý sản phẩm', '2 sản phẩm nổi bật', 'Thu dịu dàng', 'Bó hoa', '890.000', 'Nắng trong veo', '550.000'])
            ->assertDontSeeText('Cúc họa mi tháng mười')
            ->assertSeeText('Hiển thị 2 / 3 sản phẩm');
    }

    public function test_empty_shop_shows_gentle_notes(): void
    {
        app()->setLocale('vi');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Tiệm chưa có đơn hàng nào.')
            ->assertSeeText('Chưa có sản phẩm nổi bật nào đang bán.')
            ->assertSeeText('Sáng nay chưa có bó hoa nào cần giao trước 12:00.');
    }

    public function test_guests_cannot_open_the_dashboard(): void
    {
        auth('admin')->logout();

        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    private function completedOrder(int $total, string $completedAt): Order
    {
        $order = Order::factory()->create(['order_status' => OrderStatus::Completed, 'total_amount' => $total]);
        OrderStatusLog::factory()->for($order)->create(['status' => OrderStatus::Completed])
            ->forceFill(['created_at' => Carbon::parse($completedAt)])->save();

        return $order;
    }

    private function orderForToday(string $timeSlot, int $quantity, OrderStatus $status = OrderStatus::Preparing, PaymentMethod $method = PaymentMethod::Cod): void
    {
        $order = Order::factory()->withDeliveryDetail()->create(['order_status' => $status, 'payment_method' => $method]);
        $order->deliveryDetail->update(['delivery_date' => now()->toDateString(), 'time_slot' => $timeSlot]);
        OrderItem::factory()->for($order)->create(['item_type' => CartItemType::Flower, 'quantity' => $quantity]);
        OrderItem::factory()->for($order)->create(['item_type' => CartItemType::Addon, 'quantity' => 9]);
    }
}
