<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;

final class ProductImageRepository implements ProductImageRepositoryInterface
{
    public function create(array $attributes): ProductImage
    {
        return ProductImage::create($attributes);
    }

    public function update(ProductImage $image, array $attributes): void
    {
        $image->update($attributes);
    }

    public function delete(ProductImage $image): void
    {
        $image->delete();
    }

    public function hasPrimary(int $productId): bool
    {
        return ProductImage::query()->where('product_id', $productId)->where('is_primary', true)->exists();
    }

    public function nextSortOrder(int $productId): int
    {
        $currentMax = ProductImage::query()->where('product_id', $productId)->max('sort_order');

        return $currentMax === null ? 0 : (int) $currentMax + 1;
    }

    public function clearPrimary(int $productId): void
    {
        ProductImage::query()->where('product_id', $productId)->where('is_primary', true)->update(['is_primary' => false]);
    }

    public function findFirstByProduct(int $productId): ?ProductImage
    {
        return ProductImage::query()->where('product_id', $productId)->orderBy('sort_order')->orderBy('id')->first();
    }
}
