<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Kiểu hoa đang hiển thị (type = flower_type) theo tên, kèm products_count = số sản phẩm đang bán.
     *
     * @return Collection<int, Category>
     */
    public function findActiveFlowerTypesWithProductCount(): Collection;

    /**
     * Dịp gửi thương đang hiển thị (type = occasion), theo thứ tự tạo.
     *
     * @return Collection<int, Category>
     */
    public function findActiveOccasions(): Collection;
}
