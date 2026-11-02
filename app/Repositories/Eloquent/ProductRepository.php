<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;

final class ProductRepository implements ProductRepositoryInterface
{
    public function loadSizesAndImages(Product $product): Product
    {
        return $product->load(['sizes', 'images.size']);
    }
}
