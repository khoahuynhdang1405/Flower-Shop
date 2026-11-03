<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

final class ProductRepository implements ProductRepositoryInterface
{
    public function loadSizesAndImages(Product $product): Product
    {
        return $product->load(['sizes', 'images.size']);
    }

    public function findFeaturedForSale(int $limit): Collection
    {
        return Product::query()
            ->where('is_featured', true)
            ->where('is_active', true)
            ->with(['category', 'defaultSize', 'primaryImage'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
