<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Cart\AddAddonToCartAction;
use App\Actions\Cart\AddFlowerToCartAction;
use App\Actions\Cart\CalculateCartSubtotalAction;
use App\Actions\Cart\RemoveAddonFromCartAction;
use App\Actions\Cart\RemoveFlowerFromCartAction;
use App\Actions\Cart\UpdateCartAddonQuantityAction;
use App\Actions\Cart\UpdateCartFlowerQuantityAction;
use App\DTOs\Cart\AddFlowerToCartData;
use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Giỏ hàng chạy thật: Action → SessionCartRepository (session) + repository Eloquent (database).
 */
class SessionCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_from_the_design_is_stored_in_session_and_subtotals_800_000(): void
    {
        $whisper = Product::factory()->create(['base_price' => 480000]);
        $small = ProductSize::factory()->for($whisper)->create(['size_name' => 'Nhỏ', 'price' => 480000]);
        $medium = ProductSize::factory()->for($whisper)->create(['size_name' => 'Vừa', 'price' => 680000, 'is_default' => true]);
        $chocolate = Addon::factory()->create(['name' => 'Chocolate ngọt ngào', 'price' => 120000]);
        $card = Addon::factory()->free()->create(['name' => 'Thiệp viết tay']);

        app(AddFlowerToCartAction::class)->execute(new AddFlowerToCartData($whisper->id, $medium->id, 1));
        app(AddAddonToCartAction::class)->execute($chocolate->id, 1);
        app(AddAddonToCartAction::class)->execute($card->id, 1);

        $this->assertSame(800000, app(CalculateCartSubtotalAction::class)->execute());

        app(AddFlowerToCartAction::class)->execute(new AddFlowerToCartData($whisper->id, $medium->id, 1));
        app(AddFlowerToCartAction::class)->execute(new AddFlowerToCartData($whisper->id, $small->id, 1));

        $this->assertSame([
            "{$whisper->id}:{$medium->id}" => 2,
            "{$whisper->id}:{$small->id}" => 1,
        ], session('cart.flowers'));
        $this->assertSame([$chocolate->id => 1, $card->id => 1], session('cart.addons'));
        $this->assertSame(2 * 680000 + 480000 + 120000, app(CalculateCartSubtotalAction::class)->execute());
    }

    public function test_changing_quantities_and_removing_items_updates_the_session_and_subtotal(): void
    {
        $whisper = Product::factory()->create();
        $small = ProductSize::factory()->for($whisper)->create(['price' => 480000]);
        $medium = ProductSize::factory()->for($whisper)->create(['price' => 680000, 'is_default' => true]);
        $chocolate = Addon::factory()->create(['price' => 120000]);
        $card = Addon::factory()->free()->create();
        app(AddFlowerToCartAction::class)->execute(new AddFlowerToCartData($whisper->id, $medium->id, 1));
        app(AddFlowerToCartAction::class)->execute(new AddFlowerToCartData($whisper->id, $small->id, 1));
        app(AddAddonToCartAction::class)->execute($chocolate->id, 1);
        app(AddAddonToCartAction::class)->execute($card->id, 1);

        app(UpdateCartFlowerQuantityAction::class)->execute($whisper->id, $medium->id, 3);
        app(UpdateCartAddonQuantityAction::class)->execute($chocolate->id, 2);
        app(RemoveFlowerFromCartAction::class)->execute($whisper->id, $small->id);
        app(RemoveFlowerFromCartAction::class)->execute($whisper->id, $small->id);
        app(RemoveAddonFromCartAction::class)->execute($card->id);

        $this->assertSame(["{$whisper->id}:{$medium->id}" => 3], session('cart.flowers'));
        $this->assertSame([$chocolate->id => 2], session('cart.addons'));
        $this->assertSame(3 * 680000 + 2 * 120000, app(CalculateCartSubtotalAction::class)->execute());
    }

    public function test_items_hidden_after_being_added_drop_out_of_the_subtotal(): void
    {
        $product = Product::factory()->create(['base_price' => 550000]);
        $teddy = Addon::factory()->create(['price' => 150000]);
        app(AddFlowerToCartAction::class)->execute(new AddFlowerToCartData($product->id, null, 2));
        app(AddAddonToCartAction::class)->execute($teddy->id, 1);

        $this->assertSame(2 * 550000 + 150000, app(CalculateCartSubtotalAction::class)->execute());

        $product->update(['is_active' => false]);
        $teddy->update(['is_active' => false]);

        $this->assertSame(0, app(CalculateCartSubtotalAction::class)->execute());
    }
}
