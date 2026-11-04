<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Product\ListCatalogProductsAction;
use App\DTOs\Product\CatalogFilterData;
use App\Enums\PriceRange;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use PHPUnit\Framework\TestCase;

class ListCatalogProductsActionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_asks_the_repository_for_nine_filtered_products_per_page(): void
    {
        $filters = new CatalogFilterData(typeSlugs: ['bo-hoa'], priceRange: PriceRange::From500To800);
        $page = Mockery::mock(LengthAwarePaginator::class);
        $products = Mockery::mock(ProductRepositoryInterface::class);
        $products->shouldReceive('paginateForCatalog')->once()->with($filters, 9)->andReturn($page);

        $this->assertSame($page, (new ListCatalogProductsAction($products))->execute($filters));
    }
}
