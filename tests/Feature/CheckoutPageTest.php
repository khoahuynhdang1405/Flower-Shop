<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_empty_cart_goes_back_to_the_cart_page(): void
    {
        $this->get(route('checkout'))->assertRedirect(route('cart.show'));
    }

    public function test_order_from_the_design_is_delivered_free_and_totals_760_000_with_lamoi(): void
    {
        [$whisper, $medium] = $this->whisper();
        $chocolate = Addon::factory()->create(['name' => 'Chocolate', 'price' => 120000]);
        $card = Addon::factory()->free()->create(['name' => 'Thiệp viết tay']);
        Promotion::factory()->create(['code' => 'LAMOI', 'discount_value' => 40000, 'min_order_amount' => 0]);
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1, 'addons' => [$chocolate->id, $card->id]]);
        $this->post(route('cart.promotion.store'), ['code' => 'LAMOI']);

        $this->get(route('checkout'))
            ->assertOk()
            ->assertSee('aria-current="step"', false)
            ->assertSeeTextInOrder([
                'Gửi hoa, gửi yêu thương',
                'Thông tin người đặt',
                'Hoa sẽ đến với ai?',
                'Hẹn một ngày thật đẹp',
                'Những điều muốn gửi gắm',
                'Phương thức thanh toán',
                'Đơn hoa của bạn',
                'Lời thì thầm',
                'Cỡ vừa · Số lượng 1',
                '680.000 VNĐ',
                'Chocolate · 1',
                '120.000 VNĐ',
                'Thiệp viết tay · 1',
                'Miễn phí',
                'Tạm tính',
                '800.000 VNĐ',
                'Ưu đãi LAMOI',
                '−40.000 VNĐ',
                'Giao hoa · TP. Hồ Chí Minh',
                '0 VNĐ',
                'Tổng thanh toán',
                '760.000 VNĐ',
                'Đặt hoa · 760.000 VNĐ',
            ])
            ->assertSee('name="time_slot" value="14:00-16:00"', false)
            ->assertSee('name="payment_method" value="vnpay" class="peer sr-only" checked', false)
            ->assertSeeText('Gọi 0378599249');
    }

    public function test_delivery_date_is_limited_from_today_to_90_days_ahead(): void
    {
        $this->travelTo('2026-11-09 10:00');
        [$whisper, $medium] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);

        $this->get(route('checkout'))
            ->assertSee('min="2026-11-09" max="2027-02-07"', false);
    }

    public function test_schedule_has_a_date_picker_and_four_required_time_slots(): void
    {
        [$whisper, $medium] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);

        $response = $this->get(route('checkout'))
            ->assertOk()
            ->assertSee('data-date-picker-input', false)
            ->assertSeeText('Chọn ngày giao hoa')
            ->assertSeeTextInOrder(['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'])
            ->assertSee('data-timezone="Asia/Ho_Chi_Minh"', false);

        preg_match_all('/name="time_slot" value="([^"]+)" class="sr-only"\s+required>/', $response->getContent(), $requiredSlots);

        $this->assertSame(['08:00-10:00', '10:00-12:00', '14:00-16:00', '16:00-18:00'], $requiredSlots[1]);
    }

    public function test_card_message_shows_how_many_of_500_characters_are_used(): void
    {
        [$whisper, $medium] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);

        $response = $this->withSession(['_old_input' => ['card_message' => 'Chúc Trang một ngày thật dịu dàng.']])
            ->get(route('checkout'))
            ->assertSeeTextInOrder(['Người gửi', 'Người nhận', 'Lời nhắn', 'Chúc Trang một ngày thật dịu dàng.', 'Lời nhắn sẽ được giữ riêng tư', '34/500'])
            ->assertSee('data-char-count="card_message-counter"', false);

        $this->assertMatchesRegularExpression('/data-max="500"\s*>34\/500</', $response->getContent());
    }

    public function test_card_message_counter_is_highlighted_within_the_last_10_percent(): void
    {
        [$whisper, $medium] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);

        $response = $this->withSession(['_old_input' => ['card_message' => str_repeat('a', 450)]])->get(route('checkout'));

        $this->assertMatchesRegularExpression('/data-max="500"\s+data-near-limit\s*>450\/500</', $response->getContent());
    }

    public function test_chosen_date_and_time_slot_are_kept_after_a_failed_submit(): void
    {
        [$whisper, $medium] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);

        $this->withSession(['_old_input' => ['delivery_date' => '2026-12-24', 'time_slot' => '14:00-16:00']])
            ->get(route('checkout'))
            ->assertSee('value="2026-12-24"', false)
            ->assertSee('value="14:00-16:00" class="sr-only" checked', false);
    }

    public function test_below_free_delivery_the_flat_inner_city_fee_is_added(): void
    {
        [$whisper,, $small] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $small->id, 'quantity' => 1]);

        $this->get(route('checkout'))
            ->assertOk()
            ->assertSeeTextInOrder(['Tạm tính', '480.000 VNĐ', 'Giao hoa · TP. Hồ Chí Minh', '25.000 VNĐ', 'Tổng thanh toán', '505.000 VNĐ']);
    }

    public function test_free_delivery_threshold_is_checked_before_the_discount(): void
    {
        [$whisper, $medium] = $this->whisper();
        $chocolate = Addon::factory()->create(['price' => 120000]);
        Promotion::factory()->create(['code' => 'LAMOI', 'discount_value' => 40000, 'min_order_amount' => 0]);
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1, 'addons' => [$chocolate->id]]);
        $this->post(route('cart.promotion.store'), ['code' => 'LAMOI']);

        $this->get(route('checkout'))
            ->assertSeeTextInOrder(['Giao hoa · TP. Hồ Chí Minh', '0 VNĐ', 'Tổng thanh toán', '760.000 VNĐ']);
    }

    public function test_signed_in_customer_has_their_details_filled_in(): void
    {
        [$whisper, $medium] = $this->whisper();
        $user = User::factory()->create(['name' => 'Huỳnh Đăng Khoa', 'phone' => '0903456789', 'email' => 'khoa@example.com']);
        $this->actingAs($user)->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);

        $this->actingAs($user)->get(route('checkout'))
            ->assertOk()
            ->assertSee('value="Huỳnh Đăng Khoa"', false)
            ->assertSee('value="0903456789"', false)
            ->assertSee('value="khoa@example.com"', false);
    }

    /**
     * "Lời thì thầm": Nhỏ 480.000, Vừa 680.000.
     *
     * @return array{Product, ProductSize, ProductSize}
     */
    private function whisper(): array
    {
        $whisper = Product::factory()->create(['name' => 'Lời thì thầm', 'slug' => 'loi-thi-tham', 'base_price' => 480000]);
        $small = ProductSize::factory()->for($whisper)->create(['size_name' => 'Nhỏ', 'price' => 480000]);
        $medium = ProductSize::factory()->for($whisper)->create(['size_name' => 'Vừa', 'price' => 680000, 'is_default' => true]);

        return [$whisper, $medium, $small];
    }
}
