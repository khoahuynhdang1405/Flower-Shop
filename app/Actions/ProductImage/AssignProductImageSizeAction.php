<?php

declare(strict_types=1);

namespace App\Actions\ProductImage;

use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;

/**
 * Gắn ảnh vào một size của sản phẩm, hoặc chuyển về ảnh chung (null).
 */
final class AssignProductImageSizeAction
{
    public function __construct(private ProductImageRepositoryInterface $images) {}

    public function execute(ProductImage $image, ?int $productSizeId): void
    {
        $this->images->update($image, ['product_size_id' => $productSizeId]);
    }
}
