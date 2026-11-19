<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Collection\AddProductsToCollectionAction;
use App\Actions\Collection\RemoveProductFromCollectionAction;
use App\Actions\Collection\ShowCollectionProductsAction;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Thêm/bỏ sản phẩm của bộ sưu tập với repository giả: Theo mùa / Theo dịp qua pivot, Kiểu hoa chỉ xem.
 */
class CollectionProductActionsTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private CategoryRepositoryInterface&MockInterface $categories;

    private ProductRepositoryInterface&MockInterface $products;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categories = Mockery::mock(CategoryRepositoryInterface::class);
        $this->products = Mockery::mock(ProductRepositoryInterface::class);
    }

    public function test_adding_passes_unique_ids_and_returns_how_many_were_new(): void
    {
        $season = $this->collection(CategoryType::Season);
        $this->categories->expects('attachProducts')->with($season, [3, 5])->andReturn(1);

        $this->assertSame(1, (new AddProductsToCollectionAction($this->categories))->execute($season, [3, 5, 3]));
    }

    public function test_flower_type_collections_cannot_be_changed_here(): void
    {
        $flowerType = $this->collection(CategoryType::FlowerType);
        $this->categories->expects('attachProducts')->never();
        $this->categories->expects('detachProduct')->never();

        try {
            (new AddProductsToCollectionAction($this->categories))->execute($flowerType, [1]);
            $this->fail('Adding to a flower type collection should throw.');
        } catch (DomainException) {
        }

        $this->expectException(DomainException::class);
        (new RemoveProductFromCollectionAction($this->categories))->execute($flowerType, new Product);
    }

    public function test_removing_detaches_the_product(): void
    {
        $occasion = $this->collection(CategoryType::Occasion);
        $product = new Product;
        $this->categories->expects('detachProduct')->with($occasion, $product)->andReturn(true);

        $this->assertTrue((new RemoveProductFromCollectionAction($this->categories))->execute($occasion, $product));
    }

    public function test_show_lists_linked_products_and_candidates_for_an_occasion(): void
    {
        $occasion = $this->collection(CategoryType::Occasion);
        $members = new Collection;
        $candidates = Mockery::mock(LengthAwarePaginator::class);
        $this->products->expects('findLinkedToCollection')->with($occasion)->andReturn($members);
        $this->products->expects('paginateNotLinkedToCollection')
            ->with($occasion, 'hồng', ShowCollectionProductsAction::CANDIDATES_PER_PAGE)->andReturn($candidates);

        $result = (new ShowCollectionProductsAction($this->products))->execute($occasion, 'hồng');

        $this->assertSame($members, $result['members']);
        $this->assertSame($candidates, $result['candidates']);
        $this->assertSame('hồng', $result['search']);
    }

    public function test_show_lists_flower_type_products_without_candidates(): void
    {
        $flowerType = $this->collection(CategoryType::FlowerType);
        $members = new Collection;
        $this->products->expects('findByPrimaryCategory')->with($flowerType)->andReturn($members);
        $this->products->expects('paginateNotLinkedToCollection')->never();

        $result = (new ShowCollectionProductsAction($this->products))->execute($flowerType, 'hồng');

        $this->assertSame($members, $result['members']);
        $this->assertNull($result['candidates']);
    }

    private function collection(CategoryType $type): Category
    {
        $collection = new Category;
        $collection->type = $type;

        return $collection;
    }
}
