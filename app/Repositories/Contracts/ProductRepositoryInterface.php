<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Nạp danh sách size (theo giá) và bộ ảnh (kèm size của từng ảnh) cho trang quản lý ảnh.
     */
    public function loadSizesAndImages(Product $product): Product;
}
