<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Category\ListHomeOccasionsAction;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Khối dịp gửi ở Trang chủ: tối đa 4 dịp đang hiển thị, giữ thứ tự từ repository.
 */
class ListHomeOccasionsActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_returns_at_most_four_occasions_in_order(): void
    {
        $occasions = new Collection(array_map(fn (int $id): Category => (new Category)->forceFill(['id' => $id]), range(1, 6)));
        $categories = Mockery::mock(CategoryRepositoryInterface::class);
        $categories->expects('findActiveOccasions')->andReturn($occasions);

        $result = (new ListHomeOccasionsAction($categories))->execute();

        $this->assertSame([1, 2, 3, 4], $result->pluck('id')->all());
    }
}
