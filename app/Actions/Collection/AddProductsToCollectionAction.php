<?php

declare(strict_types=1);

namespace App\Actions\Collection;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use DomainException;

/**
 * Gắn các sản phẩm đã chọn vào bộ sưu tập Theo mùa / Theo dịp; sản phẩm đã có thì bỏ qua.
 */
final class AddProductsToCollectionAction
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    /**
     * @param  list<int>  $productIds
     * @return int số sản phẩm vừa được thêm
     *
     * @throws DomainException khi bộ sưu tập là Kiểu hoa (đổi kiểu hoa ở form sản phẩm)
     */
    public function execute(Category $collection, array $productIds): int
    {
        if (! $collection->type->allowsManualProducts()) {
            throw new DomainException('Flower type collections are managed from the product form.');
        }

        return $this->categories->attachProducts($collection, array_values(array_unique($productIds)));
    }
}
