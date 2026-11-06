<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Cart\ApplyPromotionCodeAction;
use App\Actions\Cart\CalculatePromotionDiscountAction;
use App\Actions\Cart\GetCartSummaryAction;
use App\Actions\Cart\ListCartLinesAction;
use App\Enums\DiscountType;
use App\Enums\PromotionState;
use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Promotion;
use App\Repositories\Contracts\AddonRepositoryInterface;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Tóm tắt giỏ hàng (ưu đãi, phí giao, tổng) và áp mã ưu đãi, với repository giả.
 */
class CartSummaryActionsTest extends TestCase
{
    private ProductRepositoryInterface&MockInterface $products;

    private AddonRepositoryInterface&MockInterface $addons;

    private CartRepositoryInterface&MockInterface $cart;

    private PromotionRepositoryInterface&MockInterface $promotions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->products = Mockery::mock(ProductRepositoryInterface::class);
        $this->addons = Mockery::mock(AddonRepositoryInterface::class);
        $this->cart = Mockery::mock(CartRepositoryInterface::class);
        $this->promotions = Mockery::mock(PromotionRepositoryInterface::class);
    }

    public function test_fixed_discount_never_exceeds_the_subtotal(): void
    {
        $discount = new CalculatePromotionDiscountAction;

        $this->assertSame(40000, $discount->execute($this->promotion(DiscountType::Fixed, 40000), 800000));
        $this->assertSame(30000, $discount->execute($this->promotion(DiscountType::Fixed, 40000), 30000));
    }

    public function test_percentage_discount_rounds_down_and_respects_the_cap(): void
    {
        $discount = new CalculatePromotionDiscountAction;

        $this->assertSame(68333, $discount->execute($this->promotion(DiscountType::Percentage, 10), 683335));
        $this->assertSame(100000, $discount->execute($this->promotion(DiscountType::Percentage, 10, max: 100000), 1500000));
    }

    public function test_summary_from_the_design_applies_the_code_and_free_delivery(): void
    {
        $this->cartHolds(flowerPrice: 680000, chocolatePrice: 120000, withCard: true);
        $this->cart->shouldReceive('promotionCode')->andReturn('LAMOI');
        $this->promotions->shouldReceive('findActiveByCode')->with('LAMOI')->andReturn($this->promotion(DiscountType::Fixed, 40000));

        $summary = $this->summary()->execute();

        $this->assertSame(800000, $summary->subtotal);
        $this->assertSame(2, $summary->lines->itemCount());
        $this->assertSame(PromotionState::Applied, $summary->promotionState);
        $this->assertSame(40000, $summary->discount);
        $this->assertSame(0, $summary->shippingFee);
        $this->assertSame(760000, $summary->total);
    }

    public function test_below_free_delivery_threshold_the_fee_is_left_for_checkout(): void
    {
        $this->cartHolds(flowerPrice: 680000);
        $this->cart->shouldReceive('promotionCode')->andReturn(null);
        $this->promotions->shouldNotReceive('findActiveByCode');

        $summary = $this->summary()->execute();

        $this->assertNull($summary->promotionState);
        $this->assertNull($summary->shippingFee);
        $this->assertSame(680000, $summary->total);
    }

    public function test_code_below_its_minimum_or_switched_off_is_not_applied(): void
    {
        $this->cartHolds(flowerPrice: 480000);
        $this->cart->shouldReceive('promotionCode')->andReturn('CHAOHOA', 'TAT');
        $this->promotions->shouldReceive('findActiveByCode')->with('CHAOHOA')->andReturn($this->promotion(DiscountType::Percentage, 10, min: 500000));
        $this->promotions->shouldReceive('findActiveByCode')->with('TAT')->andReturn(null);

        $belowMinimum = $this->summary()->execute();
        $switchedOff = $this->summary()->execute();

        $this->assertSame(PromotionState::BelowMinimum, $belowMinimum->promotionState);
        $this->assertSame(500000, $belowMinimum->promotionMinOrder);
        $this->assertSame(0, $belowMinimum->discount);
        $this->assertSame(480000, $belowMinimum->total);
        $this->assertSame(PromotionState::Unavailable, $switchedOff->promotionState);
        $this->assertSame(0, $switchedOff->discount);
    }

    public function test_applying_a_code_stores_it_normalised(): void
    {
        $this->promotions->shouldReceive('findActiveByCode')->with('LAMOI')->andReturn($this->promotion(DiscountType::Fixed, 40000));
        $this->cart->shouldReceive('putPromotionCode')->once()->with('LAMOI');

        (new ApplyPromotionCodeAction($this->promotions, $this->cart))->execute('  lamoi ');
    }

    public function test_an_unknown_code_is_rejected(): void
    {
        $this->promotions->shouldReceive('findActiveByCode')->andReturn(null);
        $this->cart->shouldNotReceive('putPromotionCode');

        try {
            (new ApplyPromotionCodeAction($this->promotions, $this->cart))->execute('KHONGCO');
            $this->fail('Expected a validation error on [code].');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('code', $exception->errors());
        }
    }

    private function summary(): GetCartSummaryAction
    {
        return new GetCartSummaryAction(
            new ListCartLinesAction($this->products, $this->addons, $this->cart),
            new CalculatePromotionDiscountAction,
            $this->cart,
            $this->promotions,
            freeShippingFrom: 800000,
        );
    }

    private function cartHolds(int $flowerPrice, ?int $chocolatePrice = null, bool $withCard = false): void
    {
        $size = (new ProductSize)->forceFill(['id' => 21, 'product_id' => 7, 'size_name' => 'Vừa', 'price' => $flowerPrice]);
        $product = (new Product)->forceFill(['id' => 7, 'name' => 'Lời thì thầm', 'slug' => 'loi-thi-tham', 'base_price' => 480000])
            ->setRelation('sizes', new Collection([$size]))
            ->setRelation('primaryImage', null);
        $addons = array_filter([
            $chocolatePrice === null ? null : (new Addon)->forceFill(['id' => 3, 'name' => 'Chocolate ngọt ngào', 'price' => $chocolatePrice, 'is_free' => false]),
            $withCard ? (new Addon)->forceFill(['id' => 4, 'name' => 'Thiệp viết tay', 'price' => 0, 'is_free' => true]) : null,
        ]);

        $this->cart->shouldReceive('flowers')->andReturn([['product_id' => 7, 'size_id' => 21, 'quantity' => 1]]);
        $this->cart->shouldReceive('addons')->andReturn(array_fill_keys(array_map(fn (Addon $addon): int => $addon->id, $addons), 1));
        $this->products->shouldReceive('findForSaleWithSizes')->andReturn(new Collection([$product]));
        $this->addons->shouldReceive('findActiveByIds')->andReturn(new Collection(array_values($addons)));
    }

    private function promotion(DiscountType $type, int $value, int $min = 0, ?int $max = null): Promotion
    {
        return (new Promotion)->forceFill([
            'code' => 'LAMOI',
            'discount_type' => $type,
            'discount_value' => $value,
            'min_order_amount' => $min,
            'max_discount_amount' => $max,
        ]);
    }
}
