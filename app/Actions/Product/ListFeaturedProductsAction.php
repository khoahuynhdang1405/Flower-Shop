<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * "Những bó hoa được thương nhất" ở Trang chủ: sản phẩm nổi bật đang bán, tối đa 8 (2 hàng × 4).
 */
final class ListFeaturedProductsAction
{
    public const LIMIT = 8;

    public function __construct(private ProductRepositoryInterface $products) {}

    /**
     * @return Collection<int, Product>
     */
    public function execute(): Collection
    {
        return $this->products->findFeaturedForSale(self::LIMIT);
    }
}
