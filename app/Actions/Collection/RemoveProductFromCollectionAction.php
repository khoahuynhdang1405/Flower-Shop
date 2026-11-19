<?php

declare(strict_types=1);

namespace App\Actions\Collection;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use DomainException;

/**
 * Gỡ một sản phẩm khỏi bộ sưu tập Theo mùa / Theo dịp; sản phẩm vẫn còn trên cửa hàng.
 */
final class RemoveProductFromCollectionAction
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    /**
     * @return bool false khi sản phẩm vốn không có trong bộ sưu tập
     *
     * @throws DomainException khi bộ sưu tập là Kiểu hoa (đổi kiểu hoa ở form sản phẩm)
     */
    public function execute(Category $collection, Product $product): bool
    {
        if (! $collection->type->allowsManualProducts()) {
            throw new DomainException('Flower type collections are managed from the product form.');
        }

        return $this->categories->detachProduct($collection, $product);
    }
}
