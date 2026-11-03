<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Product\ListFeaturedProductsAction;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class ListFeaturedProductsActionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_asks_the_repository_for_at_most_eight_featured_products(): void
    {
        $featured = new Collection([new Product, new Product]);
        $products = Mockery::mock(ProductRepositoryInterface::class);
        $products->shouldReceive('findFeaturedForSale')->once()->with(8)->andReturn($featured);

        $this->assertSame($featured, (new ListFeaturedProductsAction($products))->execute());
    }
}
