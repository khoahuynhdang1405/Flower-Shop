<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\DTOs\Product\CatalogFilterData;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Lưới sản phẩm trang "Tất cả hoa": lọc theo bộ lọc bên trái, 9 sản phẩm/trang (3 hàng × 3).
 */
final class ListCatalogProductsAction
{
    public const PER_PAGE = 9;

    public function __construct(private ProductRepositoryInterface $products) {}

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function execute(CatalogFilterData $filters): LengthAwarePaginator
    {
        return $this->products->paginateForCatalog($filters, self::PER_PAGE);
    }
}
