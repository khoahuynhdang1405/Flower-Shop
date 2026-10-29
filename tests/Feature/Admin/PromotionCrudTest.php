<?php

namespace Tests\Feature\Admin;

use App\Enums\DiscountType;
use App\Models\Admin;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PromotionCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        $this->actingAs(Admin::factory()->create(), 'admin');
    }

    public function test_index_lists_promotions_with_stats(): void
    {
        Promotion::factory()->percentage(10, 100000)->create(['code' => 'CHAOHOA', 'min_order_amount' => 500000]);
        Promotion::factory()->paused()->create(['code' => 'BANCU50']);

        $this->get(route('admin.promotions'))
            ->assertOk()
            ->assertSeeText('CHAOHOA')
            ->assertSeeText('Tối đa 100.000 VNĐ')
            ->assertSeeText('Đơn từ 500.000 VNĐ')
            ->assertSeeText('1 đang áp dụng · 1 tạm dừng')
            ->assertSeeText('Hiển thị tất cả 2 mã ưu đãi');
    }

    public function test_index_renders_in_english(): void
    {
        app()->setLocale('en');
        Promotion::factory()->percentage(10, 100000)->create();

        $this->get(route('admin.promotions'))->assertOk()->assertSeeText('Up to 100,000 VND');
    }

    public function test_admin_can_create_percentage_promotion(): void
    {
        $this->get(route('admin.promotions.create'))->assertOk();

        $this->post(route('admin.promotions.store'), [
            'code' => ' chao hoa ',
            'discount_type' => 'percentage',
            'discount_value' => '10',
            'min_order_amount' => '500.000',
            'max_discount_amount' => '100.000',
            'is_active' => '1',
        ])
            ->assertRedirect(route('admin.promotions'))
            ->assertSessionHas('status', 'Đã thêm mã CHAOHOA.');

        $promotion = Promotion::sole();
        $this->assertSame('CHAOHOA', $promotion->code);
        $this->assertSame(DiscountType::Percentage, $promotion->discount_type);
        $this->assertSame(10, $promotion->discount_value);
        $this->assertSame(500000, $promotion->min_order_amount);
        $this->assertSame(100000, $promotion->max_discount_amount);
    }

    public function test_fixed_promotion_drops_max_discount(): void
    {
        $this->post(route('admin.promotions.store'), [
            'code' => 'THUYEUTHUONG',
            'discount_type' => 'fixed',
            'discount_value' => '50000',
            'max_discount_amount' => '80000',
        ])->assertSessionHasNoErrors();

        $promotion = Promotion::sole();
        $this->assertNull($promotion->max_discount_amount);
        $this->assertSame(0, $promotion->min_order_amount);
        $this->assertFalse($promotion->is_active);
    }

    public function test_create_validates_code_and_value(): void
    {
        Promotion::factory()->create(['code' => 'CHAOHOA']);

        $this->post(route('admin.promotions.store'), ['code' => 'CHÀO-HOA', 'discount_type' => 'percentage', 'discount_value' => '150'])
            ->assertSessionHasErrors([
                'code' => __('admin.promotions.form.code_format'),
                'discount_value' => __('admin.promotions.form.percent_range'),
            ]);

        $this->post(route('admin.promotions.store'), ['code' => 'chaohoa', 'discount_type' => 'fixed', 'discount_value' => '500'])
            ->assertSessionHasErrors([
                'code' => __('admin.promotions.form.code_taken'),
                'discount_value' => __('admin.promotions.form.amount_min'),
            ]);

        $this->assertSame(1, Promotion::count());
    }

    public function test_admin_can_update_and_pause_promotion(): void
    {
        $promotion = Promotion::factory()->create(['code' => 'LAHE2026', 'discount_value' => 30000]);

        $this->get(route('admin.promotions.edit', $promotion))->assertOk()->assertSee('value="LAHE2026"', false);

        $this->put(route('admin.promotions.update', $promotion), [
            'code' => 'LAHE2026',
            'discount_type' => 'fixed',
            'discount_value' => '40000',
            'min_order_amount' => '500000',
        ])->assertRedirect(route('admin.promotions'))->assertSessionHasNoErrors();

        $promotion->refresh();
        $this->assertSame(40000, $promotion->discount_value);
        $this->assertFalse($promotion->is_active);
    }

    public function test_admin_can_delete_unused_promotion(): void
    {
        $promotion = Promotion::factory()->create();

        $this->delete(route('admin.promotions.destroy', $promotion))->assertRedirect(route('admin.promotions'));

        $this->assertModelMissing($promotion);
    }

    public function test_promotion_used_on_orders_cannot_be_deleted(): void
    {
        $promotion = Promotion::factory()->create(['code' => 'CHAOHOA']);
        DB::table('orders')->insert([
            'order_code' => '#LH1028',
            'subtotal' => 690000,
            'total_amount' => 640000,
            'promotion_id' => $promotion->id,
            'payment_method' => 'cod',
        ]);

        $this->from(route('admin.promotions'))
            ->delete(route('admin.promotions.destroy', $promotion))
            ->assertSessionHas('error', __('admin.promotions.flash.has_orders', ['code' => 'CHAOHOA']));

        $this->assertModelExists($promotion);
    }
}
