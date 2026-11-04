<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\Product\CatalogFilterData;
use App\Enums\FlowerColor;
use App\Enums\PriceRange;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogFilterTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepositoryInterface $products;

    protected function setUp(): void
    {
        parent::setUp();

        $this->products = $this->app->make(ProductRepositoryInterface::class);
    }

    public function test_without_filters_it_lists_every_product_on_sale(): void
    {
        Product::factory()->count(2)->create();
        Product::factory()->hidden()->create(['name' => 'Đã ngừng bán']);

        $page = $this->products->paginateForCatalog(new CatalogFilterData, 9);

        $this->assertSame(2, $page->total());
        $this->assertNotContains('Đã ngừng bán', $page->pluck('name'));
    }

    public function test_it_filters_by_any_of_the_chosen_flower_types(): void
    {
        $bouquets = Category::factory()->flowerType()->create(['slug' => 'bo-hoa']);
        $vases = Category::factory()->flowerType()->create(['slug' => 'hoa-cam-binh']);
        $baskets = Category::factory()->flowerType()->create(['slug' => 'gio-hop-hoa']);
        Product::factory()->for($bouquets)->create(['name' => 'Nắng trong veo']);
        Product::factory()->for($vases)->create(['name' => 'Một ngày bình yên']);
        Product::factory()->for($baskets)->create(['name' => 'Mây trắng']);

        $page = $this->products->paginateForCatalog(new CatalogFilterData(typeSlugs: ['bo-hoa', 'hoa-cam-binh']), 9);

        $this->assertEqualsCanonicalizing(['Nắng trong veo', 'Một ngày bình yên'], $page->pluck('name')->all());
    }

    public function test_it_filters_by_occasion_through_the_pivot(): void
    {
        $birthday = Category::factory()->create(['slug' => 'sinh-nhat']);
        $love = Category::factory()->create(['slug' => 'tinh-yeu']);
        Product::factory()->hasAttached([$birthday, $love], [], 'occasions')->create(['name' => 'Nắng trong veo']);
        Product::factory()->hasAttached($love, [], 'occasions')->create(['name' => 'Hồng trong tim']);
        Product::factory()->create(['name' => 'Chưa gắn dịp']);

        $page = $this->products->paginateForCatalog(new CatalogFilterData(occasionSlugs: ['sinh-nhat']), 9);

        $this->assertSame(['Nắng trong veo'], $page->pluck('name')->all());
    }

    public function test_a_flower_type_slug_does_not_match_as_an_occasion(): void
    {
        $bouquets = Category::factory()->flowerType()->create(['slug' => 'bo-hoa']);
        Product::factory()->for($bouquets)->hasAttached($bouquets, [], 'occasions')->create();

        $page = $this->products->paginateForCatalog(new CatalogFilterData(occasionSlugs: ['bo-hoa']), 9);

        $this->assertSame(0, $page->total());
    }

    public function test_it_filters_by_any_of_the_chosen_colors(): void
    {
        Product::factory()->hasColors(1, ['color' => FlowerColor::Pink])->create(['name' => 'Lời thì thầm']);
        Product::factory()->hasColors(1, ['color' => FlowerColor::White])->create(['name' => 'Mây trắng']);
        Product::factory()->hasColors(1, ['color' => FlowerColor::Red])->create(['name' => 'Hồng trong tim']);
        Product::factory()->create(['name' => 'Chưa chọn sắc']);

        $page = $this->products->paginateForCatalog(new CatalogFilterData(colors: [FlowerColor::Pink, FlowerColor::White]), 9);

        $this->assertEqualsCanonicalizing(['Lời thì thầm', 'Mây trắng'], $page->pluck('name')->all());
    }

    public function test_it_compares_the_price_range_with_the_default_size_price(): void
    {
        $inRange = Product::factory()->create(['name' => 'Lời thì thầm', 'base_price' => 450000]);
        ProductSize::factory()->for($inRange)->create(['price' => 680000, 'is_default' => true]);
        ProductSize::factory()->for($inRange)->create(['price' => 480000]);
        $outOfRange = Product::factory()->create(['name' => 'Mây trắng', 'base_price' => 600000]);
        ProductSize::factory()->for($outOfRange)->create(['price' => 950000, 'is_default' => true]);

        $page = $this->products->paginateForCatalog(new CatalogFilterData(priceRange: PriceRange::From500To800), 9);

        $this->assertSame(['Lời thì thầm'], $page->pluck('name')->all());
    }

    public function test_it_falls_back_to_base_price_when_there_is_no_default_size(): void
    {
        Product::factory()->create(['name' => 'Gửi một niềm vui', 'base_price' => 480000]);
        Product::factory()->create(['name' => 'Nắng trong veo', 'base_price' => 550000]);

        $page = $this->products->paginateForCatalog(new CatalogFilterData(priceRange: PriceRange::Under500), 9);

        $this->assertSame(['Gửi một niềm vui'], $page->pluck('name')->all());
    }

    public function test_the_lower_bound_belongs_to_the_range_and_the_upper_bound_does_not(): void
    {
        Product::factory()->create(['name' => 'Đúng 800.000', 'base_price' => 800000]);
        Product::factory()->create(['name' => 'Đúng 1.200.000', 'base_price' => 1200000]);

        $this->assertSame(['Đúng 800.000'], $this->products->paginateForCatalog(new CatalogFilterData(priceRange: PriceRange::From800To1200), 9)->pluck('name')->all());
        $this->assertSame(['Đúng 1.200.000'], $this->products->paginateForCatalog(new CatalogFilterData(priceRange: PriceRange::Over1200), 9)->pluck('name')->all());
    }

    public function test_filter_groups_are_combined_with_and(): void
    {
        $bouquets = Category::factory()->flowerType()->create(['slug' => 'bo-hoa']);
        $birthday = Category::factory()->create(['slug' => 'sinh-nhat']);
        Product::factory()->for($bouquets)->hasAttached($birthday, [], 'occasions')->create(['name' => 'Khớp cả ba', 'base_price' => 550000]);
        Product::factory()->for($bouquets)->hasAttached($birthday, [], 'occasions')->create(['name' => 'Sai giá', 'base_price' => 950000]);
        Product::factory()->for($bouquets)->create(['name' => 'Không có dịp', 'base_price' => 550000]);

        $page = $this->products->paginateForCatalog(
            new CatalogFilterData(typeSlugs: ['bo-hoa'], occasionSlugs: ['sinh-nhat'], priceRange: PriceRange::From500To800),
            9,
        );

        $this->assertSame(['Khớp cả ba'], $page->pluck('name')->all());
    }

    public function test_it_paginates_newest_first(): void
    {
        $products = Product::factory()->count(3)->create();

        $page = $this->products->paginateForCatalog(new CatalogFilterData, 2);

        $this->assertSame(3, $page->total());
        $this->assertSame([$products[2]->id, $products[1]->id], $page->pluck('id')->all());
    }
}
