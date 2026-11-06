<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Product\ShowProductDetailAction;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use PHPUnit\Framework\TestCase;

class ShowProductDetailActionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_returns_the_product_on_sale_with_the_given_slug(): void
    {
        $product = new Product;
        $products = Mockery::mock(ProductRepositoryInterface::class);
        $products->shouldReceive('findForSaleBySlug')->once()->with('loi-thi-tham')->andReturn($product);

        $this->assertSame($product, (new ShowProductDetailAction($products))->execute('loi-thi-tham'));
    }

    public function test_it_reports_not_found_when_no_product_is_on_sale_with_the_slug(): void
    {
        $products = Mockery::mock(ProductRepositoryInterface::class);
        $products->shouldReceive('findForSaleBySlug')->once()->with('khong-ton-tai')->andReturnNull();

        $this->expectException(ModelNotFoundException::class);

        (new ShowProductDetailAction($products))->execute('khong-ton-tai');
    }
}
