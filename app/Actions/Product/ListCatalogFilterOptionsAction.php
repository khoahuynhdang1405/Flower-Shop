<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Lựa chọn cho cột "Lọc hoa" lấy từ DB: Kiểu hoa (kèm số sản phẩm) và Dịp gửi thương.
 */
final class ListCatalogFilterOptionsAction
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    /**
     * @return array{flowerTypes: Collection<int, Category>, occasions: Collection<int, Category>}
     */
    public function execute(): array
    {
        return [
            'flowerTypes' => $this->categories->findActiveFlowerTypesWithProductCount(),
            'occasions' => $this->categories->findActiveOccasions(),
        ];
    }
}
