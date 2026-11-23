<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Enums\Season;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Hoa của một mùa (trang "Hoa theo mùa"): sản phẩm đang bán trong bộ sưu tập Theo mùa tương ứng.
 */
final class ListSeasonProductsAction
{
    public function __construct(private ProductRepositoryInterface $products) {}

    /**
     * @return Collection<int, Product>
     */
    public function execute(Season $season): Collection
    {
        return $this->products->findForSaleInCollection($season->collectionSlug());
    }
}
