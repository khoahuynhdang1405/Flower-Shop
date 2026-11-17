<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\CustomerGroup;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CustomerPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        $this->travelTo(Carbon::parse('2026-10-07 09:00:00'));
    }

    public function test_guests_and_customers_cannot_open_the_page(): void
    {
        $customer = User::factory()->create();

        $this->get(route('admin.customers'))->assertRedirect(route('admin.login'));
        $this->actingAs($customer)->get(route('admin.customers.show', $customer))->assertRedirect(route('admin.login'));
        $this->put(route('admin.customers.update', $customer), [])->assertRedirect(route('admin.login'));
    }

    public function test_list_shows_customers_with_order_summary_and_stats(): void
    {
        $minhAnh = User::factory()->create([
            'name' => 'Nguyễn Minh Anh', 'phone' => '0901234567', 'email' => 'minhanh@gmail.com',
            'customer_group' => CustomerGroup::BestFriend, 'created_at' => now()->subMonth(),
        ]);
        $this->order($minhAnh, OrderStatus::Completed, 1200000, now()->subDays(3));
        $this->order($minhAnh, OrderStatus::Completed, 690000, now()->subDay());
        $this->order($minhAnh, OrderStatus::Shipping, 550000, now());
        $this->order($minhAnh, OrderStatus::Cancelled, 990000, now());
        User::factory()->create(['name' => 'Vũ Thảo Nguyên', 'created_at' => now()->subDay()]);
        User::factory()->create(['name' => 'Nguyễn Gia Bảo']);

        $this->asAdmin()
            ->get(route('admin.customers'))
            ->assertOk()
            ->assertSee('<span aria-current="page">Khách hàng</span>', false)
            ->assertSeeText('Những người bạn của Lá')
            ->assertSeeTextInOrder(['Tổng khách hàng', '03'])
            ->assertSeeText('07/10/2026 · 0 so với hôm qua')
            ->assertSeeText('33% tổng khách hàng')
            ->assertSeeTextInOrder(['Nguyễn Minh Anh', 'KH'.str_pad((string) $minhAnh->id, 4, '0', STR_PAD_LEFT), '0901234567', 'minhanh@gmail.com', '3', '1.890.000', 'Bạn thân của Lá', '07/10/2026'])
            ->assertSeeText('Chưa có đơn')
            ->assertSeeText('Hiển thị 3 / 3 khách hàng');
    }

    public function test_list_searches_by_name_phone_email_or_customer_code(): void
    {
        $linh = User::factory()->create(['name' => 'Đỗ Khánh Linh', 'phone' => '0905678901', 'email' => 'khanhlinh@gmail.com']);
        User::factory()->create(['name' => 'Bùi Thanh Huyền', 'phone' => '0907890123', 'email' => 'thanhhuyen@gmail.com']);

        foreach (['khánh linh', '0905 678', 'KHANHLINH@', 'KH'.str_pad((string) $linh->id, 4, '0', STR_PAD_LEFT)] as $search) {
            $this->asAdmin()
                ->get(route('admin.customers', ['q' => $search]))
                ->assertOk()
                ->assertSeeText('Đỗ Khánh Linh')
                ->assertDontSeeText('Bùi Thanh Huyền');
        }
    }

    public function test_list_filters_by_group_and_sorts_by_spending(): void
    {
        $small = User::factory()->create(['name' => 'Lê Ngọc Mai', 'customer_group' => CustomerGroup::Loyal]);
        $big = User::factory()->create(['name' => 'Phạm Thu Hà', 'customer_group' => CustomerGroup::Loyal]);
        User::factory()->create(['name' => 'Nguyễn Gia Bảo']);
        $this->order($small, OrderStatus::Completed, 550000);
        $this->order($big, OrderStatus::Completed, 4250000);

        $this->asAdmin()
            ->get(route('admin.customers', ['group' => 'loyal', 'sort' => 'spending']))
            ->assertOk()
            ->assertSeeTextInOrder(['Phạm Thu Hà', 'Lê Ngọc Mai'])
            ->assertDontSeeText('Nguyễn Gia Bảo')
            ->assertSee('<option value="loyal" selected>', false);
    }

    public function test_invalid_filters_are_rejected_and_empty_filters_show_a_note(): void
    {
        User::factory()->create(['name' => 'Nguyễn Gia Bảo']);

        $this->asAdmin()->get(route('admin.customers', ['group' => 'vip']))->assertSessionHasErrors('group');

        $this->get(route('admin.customers', ['q' => 'không ai tên này']))
            ->assertOk()
            ->assertSeeText('Không có khách nào khớp với bộ lọc.')
            ->assertSeeText('Xóa bộ lọc');
    }

    public function test_list_paginates_eight_customers_per_page(): void
    {
        User::factory()->count(10)->create();

        $this->asAdmin()
            ->get(route('admin.customers', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('Hiển thị 2 / 10 khách hàng');
    }

    public function test_show_page_lists_details_and_recent_orders(): void
    {
        $customer = User::factory()->create([
            'name' => 'Trần Hoàng Nam', 'email' => 'hoangnam@gmail.com',
            'default_shipping_address' => '12 Võ Văn Tần', 'default_shipping_district' => 'Quận 3',
            'created_at' => Carbon::parse('2026-09-12'),
        ]);
        $order = $this->order($customer, OrderStatus::Completed, 690000);
        OrderItem::factory()->for($order)->create(['item_name' => 'Lời thì thầm']);

        $this->asAdmin()
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSeeText('Trần Hoàng Nam')
            ->assertSeeText('Gắn bó từ tháng 09/2026')
            ->assertSeeText('12 Võ Văn Tần, Quận 3')
            ->assertSeeTextInOrder([$order->order_code, 'Lời thì thầm', '690.000', 'Hoàn thành'])
            ->assertSee('id="edit"', false);
    }

    public function test_admin_updates_customer_details_and_group(): void
    {
        $customer = User::factory()->create(['name' => 'Ai đó', 'email' => 'aido@gmail.com']);

        $this->asAdmin()
            ->put(route('admin.customers.update', $customer), [
                'name' => '  Nguyễn Minh Anh ',
                'phone' => '0901 234.567',
                'birthday' => '1998-03-08',
                'customer_group' => 'best_friend',
                'email' => 'doi@gmail.com',
            ])
            ->assertRedirect(route('admin.customers.show', $customer))
            ->assertSessionHas('status', 'Đã lưu thông tin của Nguyễn Minh Anh.');

        $customer->refresh();
        $this->assertSame('Nguyễn Minh Anh', $customer->name);
        $this->assertSame('0901234567', $customer->phone);
        $this->assertSame('1998-03-08', $customer->birthday->format('Y-m-d'));
        $this->assertSame(CustomerGroup::BestFriend, $customer->customer_group);
        $this->assertSame('aido@gmail.com', $customer->email);
    }

    public function test_admin_can_change_group_of_a_customer_without_phone(): void
    {
        $customer = User::factory()->google()->create(['name' => 'Khoa Anh', 'phone' => null]);

        $this->asAdmin()
            ->put(route('admin.customers.update', $customer), ['name' => 'Khoa Anh', 'phone' => '', 'customer_group' => 'loyal'])
            ->assertSessionHasNoErrors();

        $this->assertNull($customer->refresh()->phone);
        $this->assertSame(CustomerGroup::Loyal, $customer->customer_group);
    }

    public function test_completing_an_order_upgrades_the_customer_group(): void
    {
        $customer = User::factory()->create();
        $this->order($customer, OrderStatus::Completed, 9_500_000);
        $order = $this->order($customer, OrderStatus::Shipping, 900_000);

        $this->asAdmin()->post(route('admin.orders.advance', $order))->assertSessionHas('status');

        $this->assertSame(OrderStatus::Completed, $order->refresh()->order_status);
        $this->assertSame(CustomerGroup::Loyal, $customer->refresh()->customer_group);
    }

    public function test_update_validates_input(): void
    {
        $customer = User::factory()->create();

        $this->asAdmin()
            ->put(route('admin.customers.update', $customer), ['name' => '', 'phone' => '123', 'birthday' => '2030-01-01', 'customer_group' => 'vip'])
            ->assertSessionHasErrors([
                'name' => __('account.profile.name_required'),
                'phone' => __('auth.register.phone_invalid'),
                'birthday' => __('account.profile.birthday_invalid'),
                'customer_group' => __('admin.customers.form.group_required'),
            ]);
    }

    public function test_pages_render_in_english(): void
    {
        app()->setLocale('en');
        $customer = User::factory()->create(['customer_group' => CustomerGroup::Loyal]);

        $this->asAdmin()->get(route('admin.customers'))->assertOk()->assertSeeText('Lá’s friends')->assertSeeText('Regular');
        $this->get(route('admin.customers.show', $customer))->assertOk()->assertSeeText('Recent orders');
    }

    private function asAdmin(): static
    {
        return $this->actingAs(Admin::factory()->create(), 'admin');
    }

    private function order(User $customer, OrderStatus $status, int $total, ?Carbon $createdAt = null): Order
    {
        return Order::factory()->create([
            'user_id' => $customer->id,
            'order_status' => $status,
            'subtotal' => $total,
            'total_amount' => $total,
            'created_at' => $createdAt ?? now(),
        ]);
    }
}
