<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\Product;
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

    /**
     * Gắn sản phẩm vào bộ sưu tập (pivot category_product), bỏ qua sản phẩm đã có.
     *
     * @param  list<int>  $productIds
     * @return int số sản phẩm vừa được gắn thêm
     */
    public function attachProducts(Category $collection, array $productIds): int;

    /**
     * Gỡ sản phẩm khỏi bộ sưu tập; false khi sản phẩm vốn không có trong bộ sưu tập.
     */
    public function detachProduct(Category $collection, Product $product): bool;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Category;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Category $collection, array $attributes): void;

    public function delete(Category $collection): void;

    /**
     * Còn sản phẩm nhận bộ sưu tập này làm kiểu hoa chính (products.category_id) hay không.
     */
    public function hasPrimaryProducts(Category $collection): bool;

    /**
     * Bộ sưu tập Theo mùa đang hiển thị có slug thuộc danh sách, kèm khóa là slug.
     *
     * @param  list<string>  $slugs
     * @return Collection<string, Category>
     */
    public function findActiveSeasonsBySlugs(array $slugs): Collection;
}
