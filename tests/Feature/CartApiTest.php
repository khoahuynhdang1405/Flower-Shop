<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('vi');
    }

    public function test_empty_cart(): void
    {
        $this->getJson(route('api.cart.show'))
            ->assertOk()
            ->assertExactJson(['data' => [
                'items' => [],
                'item_count' => 0,
                'subtotal' => 0,
                'promotion' => null,
                'free_shipping_from' => 800000,
                'shipping_fee' => null,
                'total' => 0,
            ]]);
    }

    public function test_adding_a_flower_with_add_ons_returns_the_cart(): void
    {
        [$whisper, $medium] = $this->whisper();
        $chocolate = Addon::factory()->create(['name' => 'Chocolate ngọt ngào', 'price' => 120000]);
        $card = Addon::factory()->free()->create(['name' => 'Thiệp viết tay']);

        $this->postJson(route('api.cart.items.store'), [
            'product_id' => $whisper->id,
            'size' => $medium->id,
            'quantity' => 1,
            'addons' => [$chocolate->id, $card->id],
        ])
            ->assertCreated()
            ->assertJsonPath('data.items.0', [
                'id' => "flower-{$whisper->id}-{$medium->id}",
                'type' => 'flower',
                'name' => 'Lời thì thầm',
                'size' => 'Vừa',
                'unit_price' => 680000,
                'quantity' => 1,
                'total' => 680000,
                'is_free' => false,
                'image_url' => null,
            ])
            ->assertJsonPath('data.items.1.id', "addon-{$chocolate->id}")
            ->assertJsonPath('data.items.2.is_free', true)
            ->assertJsonPath('data.item_count', 2)
            ->assertJsonPath('data.subtotal', 800000)
            ->assertJsonPath('data.shipping_fee', 0)
            ->assertJsonPath('data.total', 800000);
    }

    public function test_adding_an_invalid_item_returns_validation_errors(): void
    {
        $this->postJson(route('api.cart.items.store'), ['quantity' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id', 'quantity']);
    }

    public function test_changing_quantity_and_removing_items(): void
    {
        [$whisper, $medium] = $this->whisper();
        $chocolate = Addon::factory()->create(['price' => 120000]);
        $this->postJson(route('api.cart.items.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1, 'addons' => [$chocolate->id]]);
        $flowerId = "flower-{$whisper->id}-{$medium->id}";

        $this->patchJson(route('api.cart.items.update', $flowerId), ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonPath('data.subtotal', 3 * 680000 + 120000);

        $this->patchJson(route('api.cart.items.update', "addon-{$chocolate->id}"), ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('data.items.1.quantity', 2);

        $this->deleteJson(route('api.cart.items.destroy', $flowerId))
            ->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', "addon-{$chocolate->id}");
    }

    public function test_updating_an_item_that_is_not_in_the_cart_is_rejected(): void
    {
        $this->patchJson(route('api.cart.items.update', 'flower-99-1'), ['quantity' => 2])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['item' => 'Món này không còn trong giỏ của bạn.']);
    }

    public function test_malformed_item_id_is_not_found(): void
    {
        $this->patchJson('/api/cart/items/rose-1', ['quantity' => 2])->assertNotFound();
        $this->deleteJson('/api/cart/items/flower-abc')->assertNotFound();
    }

    public function test_percentage_code_is_applied_with_its_cap(): void
    {
        [$whisper, $medium] = $this->whisper();
        Promotion::factory()->percentage(10, 100000)->create(['code' => 'CHAOHOA', 'min_order_amount' => 500000]);
        $this->postJson(route('api.cart.items.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 2]);

        $this->postJson(route('api.cart.promotions.store'), ['code' => ' chaohoa '])
            ->assertOk()
            ->assertJsonPath('data.subtotal', 1360000)
            ->assertJsonPath('data.promotion', [
                'code' => 'CHAOHOA',
                'state' => 'applied',
                'discount' => 100000,
                'min_order_amount' => 500000,
                'missing_amount' => 0,
            ])
            ->assertJsonPath('data.total', 1260000);
    }

    public function test_code_below_minimum_waits_until_the_cart_qualifies(): void
    {
        [$whisper, , $small] = $this->whisper();
        Promotion::factory()->create(['code' => 'HOATUOI', 'discount_value' => 50000, 'min_order_amount' => 500000]);
        $this->postJson(route('api.cart.items.store'), ['product_id' => $whisper->id, 'size' => $small->id, 'quantity' => 1]);

        $this->postJson(route('api.cart.promotions.store'), ['code' => 'HOATUOI'])
            ->assertOk()
            ->assertJsonPath('data.promotion.state', 'below_minimum')
            ->assertJsonPath('data.promotion.discount', 0)
            ->assertJsonPath('data.promotion.missing_amount', 20000)
            ->assertJsonPath('data.total', 480000);

        $this->patchJson(route('api.cart.items.update', "flower-{$whisper->id}-{$small->id}"), ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('data.promotion.state', 'applied')
            ->assertJsonPath('data.promotion.discount', 50000)
            ->assertJsonPath('data.total', 960000 - 50000 + 0);
    }

    public function test_unknown_or_paused_code_is_rejected(): void
    {
        Promotion::factory()->paused()->create(['code' => 'TAMDUNG']);

        foreach (['KHONGCO', 'TAMDUNG'] as $code) {
            $this->postJson(route('api.cart.promotions.store'), ['code' => $code])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['code' => 'Lá chưa tìm thấy mã này, bạn kiểm tra lại giúp Lá nhé.']);
        }

        $this->getJson(route('api.cart.show'))->assertJsonPath('data.promotion', null);
    }

    public function test_code_paused_after_being_applied_is_reported_as_unavailable(): void
    {
        [$whisper, $medium] = $this->whisper();
        $promotion = Promotion::factory()->create(['code' => 'LAMOI', 'discount_value' => 40000, 'min_order_amount' => 0]);
        $this->postJson(route('api.cart.items.store'), ['product_id' => $whisper->id, 'size' => $medium->id, 'quantity' => 1]);
        $this->postJson(route('api.cart.promotions.store'), ['code' => 'LAMOI'])->assertJsonPath('data.promotion.discount', 40000);

        $promotion->update(['is_active' => false]);

        $this->getJson(route('api.cart.show'))
            ->assertJsonPath('data.promotion.state', 'unavailable')
            ->assertJsonPath('data.promotion.discount', 0)
            ->assertJsonPath('data.total', 680000);
    }

    public function test_removing_the_code(): void
    {
        Promotion::factory()->create(['code' => 'LAMOI']);
        $this->postJson(route('api.cart.promotions.store'), ['code' => 'LAMOI'])->assertOk();

        $this->deleteJson(route('api.cart.promotions.destroy'))
            ->assertOk()
            ->assertJsonPath('data.promotion', null);
    }

    /**
     * "Lời thì thầm": Nhỏ 480.000, Vừa 680.000, Lớn 880.000.
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
