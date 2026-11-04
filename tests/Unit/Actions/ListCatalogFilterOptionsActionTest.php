<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Product\ListCatalogFilterOptionsAction;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class ListCatalogFilterOptionsActionTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_returns_flower_types_and_occasions_from_the_repository(): void
    {
        $flowerTypes = new Collection([new Category]);
        $occasions = new Collection([new Category, new Category]);
        $categories = Mockery::mock(CategoryRepositoryInterface::class);
        $categories->shouldReceive('findActiveFlowerTypesWithProductCount')->once()->andReturn($flowerTypes);
        $categories->shouldReceive('findActiveOccasions')->once()->andReturn($occasions);

        $this->assertSame(
            ['flowerTypes' => $flowerTypes, 'occasions' => $occasions],
            (new ListCatalogFilterOptionsAction($categories))->execute(),
        );
    }
}
