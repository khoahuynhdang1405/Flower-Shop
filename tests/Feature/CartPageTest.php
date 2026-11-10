<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_empty_cart_invites_the_customer_to_choose_flowers(): void
    {
        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSeeText('Giỏ hoa của bạn còn trống')
            ->assertSee('href="'.route('flowers.index').'"', false)
            ->assertSee('<span aria-hidden="true">(0)</span>', false)
            ->assertDontSeeText('Gói ghém đơn hoa');
    }

    public function test_cart_from_the_design_adds_from_the_product_page_and_totals_760_000_with_lamoi(): void
    {
        [$whisper, $medium] = $this->whisper();
        $chocolate = Addon::factory()->create(['name' => 'Chocolate ngọt ngào', 'price' => 120000]);
        $card = Addon::factory()->free()->create(['name' => 'Thiệp viết tay']);
        Promotion::factory()->create(['code' => 'LAMOI', 'discount_value' => 40000, 'min_order_amount' => 0]);

        $this->post(route('cart.flowers.store'), [
            'product_id' => $whisper->id,
            'size' => $medium->id,
            'quantity' => 1,
            'addons' => [$chocolate->id, $card->id],
        ])->assertRedirect(route('cart.show'));

        $this->post(route('cart.promotion.store'), ['code' => 'lamoi'])->assertRedirect(route('cart.show'));

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSee('<span aria-hidden="true">(2)</span>', false)
            ->assertSee('aria-current="step"', false)
            ->assertSeeTextInOrder(['Lời thì thầm', 'Cỡ vừa', 'Đơn giá 680.000 VNĐ', '680.000 VNĐ'])
            ->assertSeeTextInOrder(['Chocolate ngọt ngào', 'Đơn giá 120.000 VNĐ', '120.000 VNĐ'])
            ->assertSeeTextInOrder(['Một thiệp viết tay, từ tiệm gửi cùng hoa', 'Miễn phí'])
            ->assertSee('href="'.route('flowers.show', 'loi-thi-tham').'"', false)
            ->assertSee('value="LAMOI" readonly', false)
            ->assertSeeTextInOrder([
                'Gói ghém đơn hoa', 'Đã áp dụng', 'Giảm 40.000 VNĐ cho đơn hoa của bạn.',
                'Tạm tính (2 sản phẩm)', '800.000 VNĐ',
                'Ưu đãi LAMOI', '−40.000 VNĐ',
                'Giao hoa', '0 VNĐ',
                'Tổng dự kiến', '760.000 VNĐ',
                'Tiến hành thanh toán',
            ]);
    }

    public function test_changing_quantity_and_removing_lines_from_the_cart_page(): void
    {
        [$whisper, $medium] = $this->whisper();
        $card = Addon::factory()->free()->create(['name' => 'Thiệp viết tay']);
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1, 'addons' => [$card->id]]);

        $this->patch(route('cart.flowers.update', ['product' => $whisper->id, 'size' => $medium->id]), ['quantity' => 2])
            ->assertRedirect(route('cart.show'));

        $this->get(route('cart.show'))
            ->assertSeeTextInOrder(['Tạm tính (2 sản phẩm)', '1.360.000 VNĐ', 'Giao hoa', '0 VNĐ']);

        $this->delete(route('cart.addons.destroy', $card->id))->assertRedirect(route('cart.show'));
        $this->delete(route('cart.flowers.destroy', ['product' => $whisper->id, 'size' => $medium->id]))
            ->assertRedirect(route('cart.show'))
            ->assertSessionHas('status', 'Đã bỏ món này khỏi giỏ.');

        $this->assertSame([], session('cart.flowers'));
        $this->assertSame([], session('cart.addons'));
    }

    public function test_quantity_below_one_is_rejected(): void
    {
        [$whisper, $medium] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);

        $this->from(route('cart.show'))
            ->patch(route('cart.flowers.update', ['product' => $whisper->id, 'size' => $medium->id]), ['quantity' => 0])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(1, session('cart.flowers')["{$whisper->id}:{$medium->id}"]);
    }

    public function test_below_free_delivery_the_fee_waits_for_checkout(): void
    {
        [$whisper, , $small] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $small->id, 'quantity' => 1]);

        $this->get(route('cart.show'))
            ->assertSeeTextInOrder(['Tạm tính (1 sản phẩm)', '480.000 VNĐ', 'Giao hoa', 'Tính ở bước thanh toán'])
            ->assertSeeText('Thêm 320.000 VNĐ nữa để được miễn phí giao nội thành.')
            ->assertSeeTextInOrder(['Tổng dự kiến', '480.000 VNĐ']);
    }

    public function test_unknown_code_shows_an_error_and_a_code_below_its_minimum_waits(): void
    {
        [$whisper, , $small] = $this->whisper();
        Promotion::factory()->percentage(10)->create(['code' => 'CHAOHOA', 'min_order_amount' => 500000]);
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $small->id, 'quantity' => 1]);

        $this->from(route('cart.show'))->post(route('cart.promotion.store'), ['code' => 'KHONGCO'])
            ->assertSessionHasErrors(['code' => 'Lá chưa tìm thấy mã này, bạn kiểm tra lại giúp Lá nhé.']);

        $this->post(route('cart.promotion.store'), ['code' => 'CHAOHOA']);

        $this->get(route('cart.show'))
            ->assertSeeText('Mã CHAOHOA dùng cho đơn từ 500.000 VNĐ — bạn chọn thêm 20.000 VNĐ nữa là được nhé.')
            ->assertDontSeeText('Ưu đãi CHAOHOA');

        $this->delete(route('cart.promotion.destroy'))->assertRedirect(route('cart.show'));
        $this->assertNull(session('cart.promotion_code'));
    }

    public function test_adding_a_product_no_longer_for_sale_returns_to_the_product_page_with_a_message(): void
    {
        $hidden = Product::factory()->hidden()->create(['slug' => 'da-ngung-ban']);

        $this->from(route('flowers.index'))
            ->post(route('cart.flowers.store'), ['product_id' => $hidden->id, 'quantity' => 1])
            ->assertRedirect(route('flowers.index'))
            ->assertSessionHasErrors(['product' => 'Bó hoa này hiện tiệm tạm ngưng nhận đơn, bạn chọn bó khác giúp Lá nhé.']);
    }

    public function test_cart_page_renders_in_english(): void
    {
        app()->setLocale('en');
        [$whisper, $medium] = $this->whisper();
        $this->post(route('cart.flowers.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);

        $this->get(route('cart.show'))
            ->assertOk()
            ->assertSeeText('Your flower cart')
            ->assertSeeTextInOrder(['Subtotal (1 item)', '680,000 VND', 'Estimated total']);
    }

    /**
     * "Lời thì thầm" với ba size Nhỏ 480.000 / Vừa 680.000 (mặc định) / Lớn 880.000.
     *
     * @return array{Product, ProductSize, ProductSize}
     */
    private function whisper(): array
    {
        $whisper = Product::factory()->create(['name' => 'Lời thì thầm', 'slug' => 'loi-thi-tham', 'base_price' => 480000]);
        $small = ProductSize::factory()->for($whisper)->create(['size_name' => 'Nhỏ', 'price' => 480000]);
        $medium = ProductSize::factory()->for($whisper)->create(['size_name' => 'Vừa', 'price' => 680000, 'is_default' => true]);
        ProductSize::factory()->for($whisper)->create(['size_name' => 'Lớn', 'price' => 880000]);

        return [$whisper, $medium, $small];
    }
}
