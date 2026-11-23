<?php

namespace Tests\Feature;

use App\Enums\CustomerGroup;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('account'))->assertRedirect(route('login'));
    }

    public function test_account_page_shows_logged_in_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Huỳnh Đăng Khoa',
            'phone' => '0901234567',
            'default_shipping_address' => '18 Võ Văn Tần',
            'default_shipping_district' => 'Quận 3',
            'birthday' => '2005-04-01',
        ]);
        $user->forceFill(['customer_group' => CustomerGroup::BestFriend])->save();

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSeeText('Chào Đăng Khoa, thật vui khi gặp lại bạn.')
            ->assertSeeText('ĐK')
            ->assertSee('value="0901234567"', false)
            ->assertSee('value="18 Võ Văn Tần"', false)
            ->assertSee('<option value="Quận 3" selected>', false)
            ->assertSee('value="2005-04-01"', false)
            ->assertSeeText('Bạn thân của Lá')
            ->assertSeeText('Thông tin tài khoản')
            ->assertSeeText('Những lần gửi thương')
            ->assertSeeText('Thay đổi mật khẩu')
            ->assertSee('name="current_password"', false);
    }

    public function test_orders_section_lists_own_orders_with_flower_image_and_tracking_link(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Nắng trong veo', 'slug' => 'nang-trong-veo']);
        $image = ProductImage::factory()->primary()->for($product)->create();
        $order = Order::factory()->withDeliveryDetail()->for($user)->create([
            'order_code' => '#LH-0986',
            'order_status' => OrderStatus::Completed,
            'total_amount' => 550000,
        ]);
        OrderItem::factory()->for($order)->create(['item_id' => $product->id, 'item_name' => 'Nắng trong veo', 'quantity' => 2]);
        Order::factory()->withDeliveryDetail()->for(User::factory())->create(['order_code' => '#LH-7777']);

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSeeText('Nắng trong veo')
            ->assertSeeText('#LH-0986 · '.$order->created_at->format('d/m/Y').' · 2 sản phẩm')
            ->assertSee($image->url, false)
            ->assertSee(e(route('orders.track', ['code' => '#LH-0986', 'phone' => $order->deliveryDetail->sender_phone])), false)
            ->assertSee(route('flowers.show', 'nang-trong-veo'), false)
            ->assertSeeText('Hoàn thành (1)')
            ->assertDontSeeText('#LH-7777');
    }

    public function test_orders_section_shows_empty_state_without_orders(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertSeeText('Tất cả (0)')
            ->assertDontSee('hidden class="border-t border-lh-border px-5 py-8', false);
    }

    public function test_google_only_user_does_not_see_current_password_field(): void
    {
        $this->actingAs(User::factory()->google()->create())
            ->get(route('account'))
            ->assertOk()
            ->assertSeeText(__('account.password.google_description'))
            ->assertDontSee('name="current_password"', false);
    }

    public function test_account_page_uses_full_header_and_footer(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertSeeText('Khám phá tiệm')
            ->assertSee(__('storefront.nav.cart', ['count' => 0]), false);
    }

    public function test_auth_pages_keep_compact_header_and_footer(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSeeText('Khám phá tiệm')
            ->assertDontSee(__('storefront.nav.cart', ['count' => 0]), false);
    }
}
