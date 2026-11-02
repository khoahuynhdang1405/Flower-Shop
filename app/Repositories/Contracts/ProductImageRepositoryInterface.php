<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ProductImage;

interface ProductImageRepositoryInterface
{
    /**
     * @param  array{product_id: int, product_size_id: ?int, image_path: string, is_primary: bool, sort_order: int}  $attributes
     */
    public function create(array $attributes): ProductImage;

    /**
     * @param  array{product_size_id?: ?int, is_primary?: bool}  $attributes
     */
    public function update(ProductImage $image, array $attributes): void;

    public function delete(ProductImage $image): void;

    public function hasPrimary(int $productId): bool;

    public function nextSortOrder(int $productId): int;

    public function clearPrimary(int $productId): void;

    public function findFirstByProduct(int $productId): ?ProductImage;
}
