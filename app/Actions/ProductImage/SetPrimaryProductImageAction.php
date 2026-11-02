<?php

declare(strict_types=1);

namespace App\Actions\ProductImage;

use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Đặt một ảnh làm ảnh chính; mỗi sản phẩm chỉ có một ảnh chính.
 */
final class SetPrimaryProductImageAction
{
    public function __construct(private ProductImageRepositoryInterface $images) {}

    public function execute(ProductImage $image): void
    {
        DB::transaction(function () use ($image): void {
            $this->images->clearPrimary($image->product_id);
            $this->images->update($image, ['is_primary' => true]);
        });
    }
}
