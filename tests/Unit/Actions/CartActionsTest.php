<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

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
use App\Repositories\Contracts\AddonRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Nghiệp vụ giỏ hàng, với repository giả (không chạm session / database).
 */
class CartActionsTest extends TestCase
{
    private ProductRepositoryInterface&MockInterface $products;

    private AddonRepositoryInterface&MockInterface $addons;

    private CartRepositoryInterface&MockInterface $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->products = Mockery::mock(ProductRepositoryInterface::class);
        $this->addons = Mockery::mock(AddonRepositoryInterface::class);
        $this->cart = Mockery::mock(CartRepositoryInterface::class);
    }

    public function test_adding_the_same_flower_and_size_again_adds_to_the_existing_quantity(): void
    {
        $this->products->shouldReceive('findForSaleWithSizes')->with([7])->andReturn(new Collection([$this->product(7, 480000, [21 => 680000])]));
        $this->cart->shouldReceive('flowerQuantity')->with(7, 21)->andReturn(2);
        $this->cart->shouldReceive('putFlower')->once()->with(7, 21, 3);

        $this->addFlower()->execute(new AddFlowerToCartData(7, 21, 1));
    }

    public function test_flower_quantity_is_capped_at_ninety_nine(): void
    {
        $this->products->shouldReceive('findForSaleWithSizes')->andReturn(new Collection([$this->product(7, 480000, [21 => 680000])]));
        $this->cart->shouldReceive('flowerQuantity')->andReturn(98);
        $this->cart->shouldReceive('putFlower')->once()->with(7, 21, 99);

        $this->addFlower()->execute(new AddFlowerToCartData(7, 21, 5));
    }

    public function test_product_without_sizes_is_added_without_a_size(): void
    {
        $this->products->shouldReceive('findForSaleWithSizes')->andReturn(new Collection([$this->product(7, 550000)]));
        $this->cart->shouldReceive('flowerQuantity')->with(7, null)->andReturn(0);
        $this->cart->shouldReceive('putFlower')->once()->with(7, null, 1);

        $this->addFlower()->execute(new AddFlowerToCartData(7, null, 1));
    }

    public function test_a_size_of_another_product_is_rejected(): void
    {
        $this->products->shouldReceive('findForSaleWithSizes')->andReturn(new Collection([$this->product(7, 480000, [21 => 680000])]));
        $this->cart->shouldNotReceive('putFlower');

        $this->assertValidationErrorOn('size', fn () => $this->addFlower()->execute(new AddFlowerToCartData(7, 99, 1)));
    }

    public function test_a_product_with_sizes_needs_a_size(): void
    {
        $this->products->shouldReceive('findForSaleWithSizes')->andReturn(new Collection([$this->product(7, 480000, [21 => 680000])]));
        $this->cart->shouldNotReceive('putFlower');

        $this->assertValidationErrorOn('size', fn () => $this->addFlower()->execute(new AddFlowerToCartData(7, null, 1)));
    }

    public function test_a_product_not_on_sale_is_rejected(): void
    {
        $this->products->shouldReceive('findForSaleWithSizes')->andReturn(new Collection);
        $this->cart->shouldNotReceive('putFlower');

        $this->assertValidationErrorOn('product', fn () => $this->addFlower()->execute(new AddFlowerToCartData(7, 21, 1)));
    }

    public function test_quantity_below_one_is_rejected(): void
    {
        $this->cart->shouldNotReceive('putFlower');
        $this->cart->shouldNotReceive('putAddon');

        $this->assertValidationErrorOn('quantity', fn () => $this->addFlower()->execute(new AddFlowerToCartData(7, 21, 0)));
        $this->assertValidationErrorOn('quantity', fn () => $this->addAddon()->execute(3, -1));
    }

    public function test_adding_an_addon_again_adds_to_the_existing_quantity(): void
    {
        $this->addons->shouldReceive('findActiveByIds')->with([3])->andReturn(new Collection([$this->addon(3, 120000)]));
        $this->cart->shouldReceive('addonQuantity')->with(3)->andReturn(1);
        $this->cart->shouldReceive('putAddon')->once()->with(3, 2);

        $this->addAddon()->execute(3, 1);
    }

    public function test_an_inactive_addon_is_rejected(): void
    {
        $this->addons->shouldReceive('findActiveByIds')->andReturn(new Collection);
        $this->cart->shouldNotReceive('putAddon');

        $this->assertValidationErrorOn('addon', fn () => $this->addAddon()->execute(3, 1));
    }

    public function test_subtotal_adds_flowers_by_size_price_and_addons_with_free_ones_at_zero(): void
    {
        $this->cart->shouldReceive('flowers')->andReturn([
            ['product_id' => 7, 'size_id' => 21, 'quantity' => 1],
            ['product_id' => 8, 'size_id' => null, 'quantity' => 2],
        ]);
        $this->cart->shouldReceive('addons')->andReturn([3 => 1, 4 => 1]);
        $this->products->shouldReceive('findForSaleWithSizes')->with([7, 8])->andReturn(new Collection([
            $this->product(7, 480000, [21 => 680000]),
            $this->product(8, 300000),
        ]));
        $this->addons->shouldReceive('findActiveByIds')->with([3, 4])->andReturn(new Collection([
            $this->addon(3, 120000),
            $this->addon(4, 0, isFree: true),
        ]));

        $this->assertSame(680000 + 2 * 300000 + 120000, $this->subtotal()->execute());
    }

    public function test_subtotal_skips_items_no_longer_for_sale(): void
    {
        $this->cart->shouldReceive('flowers')->andReturn([
            ['product_id' => 7, 'size_id' => 21, 'quantity' => 1],
            ['product_id' => 7, 'size_id' => 22, 'quantity' => 1],
            ['product_id' => 9, 'size_id' => null, 'quantity' => 1],
        ]);
        $this->cart->shouldReceive('addons')->andReturn([5 => 1]);
        $this->products->shouldReceive('findForSaleWithSizes')->andReturn(new Collection([$this->product(7, 480000, [21 => 680000])]));
        $this->addons->shouldReceive('findActiveByIds')->andReturn(new Collection);

        $this->assertSame(680000, $this->subtotal()->execute());
    }

    public function test_empty_cart_subtotal_is_zero(): void
    {
        $this->cart->shouldReceive('flowers')->andReturn([]);
        $this->cart->shouldReceive('addons')->andReturn([]);
        $this->products->shouldReceive('findForSaleWithSizes')->with([])->andReturn(new Collection);
        $this->addons->shouldReceive('findActiveByIds')->with([])->andReturn(new Collection);

        $this->assertSame(0, $this->subtotal()->execute());
    }

    public function test_adding_a_free_addon_always_keeps_one(): void
    {
        $this->addons->shouldReceive('findActiveByIds')->andReturn(new Collection([$this->addon(4, 0, isFree: true)]));
        $this->cart->shouldReceive('putAddon')->once()->with(4, 1);

        $this->addAddon()->execute(4, 3);
    }

    public function test_updating_a_flower_line_sets_the_new_quantity_capped_at_ninety_nine(): void
    {
        $this->cart->shouldReceive('flowerQuantity')->with(7, 21)->andReturn(2);
        $this->cart->shouldReceive('putFlower')->once()->with(7, 21, 3);
        $this->cart->shouldReceive('putFlower')->once()->with(7, 21, 99);

        $action = new UpdateCartFlowerQuantityAction($this->cart);
        $action->execute(7, 21, 3);
        $action->execute(7, 21, 150);
    }

    public function test_updating_quantity_rejects_values_below_one_and_lines_not_in_the_cart(): void
    {
        $this->cart->shouldReceive('flowerQuantity')->with(7, 21)->andReturn(0);
        $this->cart->shouldReceive('addonQuantity')->with(3)->andReturn(0);
        $this->cart->shouldNotReceive('putFlower');
        $this->cart->shouldNotReceive('putAddon');
        $updateFlower = new UpdateCartFlowerQuantityAction($this->cart);
        $updateAddon = new UpdateCartAddonQuantityAction($this->addons, $this->cart);

        $this->assertValidationErrorOn('quantity', fn () => $updateFlower->execute(7, 21, 0));
        $this->assertValidationErrorOn('quantity', fn () => $updateAddon->execute(3, 0));
        $this->assertValidationErrorOn('item', fn () => $updateFlower->execute(7, 21, 2));
        $this->assertValidationErrorOn('item', fn () => $updateAddon->execute(3, 2));
    }

    public function test_updating_an_addon_sets_the_new_quantity(): void
    {
        $this->cart->shouldReceive('addonQuantity')->with(3)->andReturn(1);
        $this->addons->shouldReceive('findActiveByIds')->with([3])->andReturn(new Collection([$this->addon(3, 120000)]));
        $this->cart->shouldReceive('putAddon')->once()->with(3, 2);

        (new UpdateCartAddonQuantityAction($this->addons, $this->cart))->execute(3, 2);
    }

    public function test_a_free_addon_quantity_cannot_go_above_one(): void
    {
        $this->cart->shouldReceive('addonQuantity')->with(4)->andReturn(1);
        $this->addons->shouldReceive('findActiveByIds')->with([4])->andReturn(new Collection([$this->addon(4, 0, isFree: true)]));
        $this->cart->shouldNotReceive('putAddon');

        $this->assertValidationErrorOn('quantity', fn () => (new UpdateCartAddonQuantityAction($this->addons, $this->cart))->execute(4, 2));
    }

    public function test_removing_forgets_the_line(): void
    {
        $this->cart->shouldReceive('forgetFlower')->once()->with(7, null);
        $this->cart->shouldReceive('forgetAddon')->once()->with(4);

        (new RemoveFlowerFromCartAction($this->cart))->execute(7, null);
        (new RemoveAddonFromCartAction($this->cart))->execute(4);
    }

    private function addFlower(): AddFlowerToCartAction
    {
        return new AddFlowerToCartAction($this->products, $this->cart);
    }

    private function addAddon(): AddAddonToCartAction
    {
        return new AddAddonToCartAction($this->addons, $this->cart);
    }

    private function subtotal(): CalculateCartSubtotalAction
    {
        return new CalculateCartSubtotalAction($this->products, $this->addons, $this->cart);
    }

    /**
     * @param  array<int, int>  $sizePrices  size id => giá
     */
    private function product(int $id, int $basePrice, array $sizePrices = []): Product
    {
        $sizes = array_map(
            fn (int $sizeId, int $price): ProductSize => (new ProductSize)->forceFill(['id' => $sizeId, 'product_id' => $id, 'price' => $price]),
            array_keys($sizePrices),
            $sizePrices,
        );

        return (new Product)->forceFill(['id' => $id, 'base_price' => $basePrice])->setRelation('sizes', new Collection($sizes));
    }

    private function addon(int $id, int $price, bool $isFree = false): Addon
    {
        return (new Addon)->forceFill(['id' => $id, 'price' => $price, 'is_free' => $isFree]);
    }

    private function assertValidationErrorOn(string $field, callable $callback): void
    {
        try {
            $callback();
            $this->fail("Expected a validation error on [{$field}].");
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }
}
