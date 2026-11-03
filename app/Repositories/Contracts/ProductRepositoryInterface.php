<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Nạp danh sách size (theo giá) và bộ ảnh (kèm size của từng ảnh) cho trang quản lý ảnh.
     */
    public function loadSizesAndImages(Product $product): Product;

    /**
     * Sản phẩm nổi bật đang bán (is_featured + is_active), mới nhất trước,
     * kèm danh mục, size mặc định và ảnh chính để hiện thẻ sản phẩm.
     *
     * @return Collection<int, Product>
     */
    public function findFeaturedForSale(int $limit): Collection;
}
