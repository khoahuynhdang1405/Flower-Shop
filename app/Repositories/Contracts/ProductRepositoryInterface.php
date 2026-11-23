<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Product\CatalogFilterData;
use App\Enums\FlowerColor;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Nạp danh sách size (theo giá), bộ ảnh (kèm size của từng ảnh) và sắc hoa cho trang quản lý ảnh.
     */
    public function loadSizesAndImages(Product $product): Product;

    /**
     * Thay toàn bộ sắc hoa của sản phẩm bằng danh sách mới.
     *
     * @param  list<FlowerColor>  $colors
     */
    public function replaceColors(Product $product, array $colors): void;

    /**
     * Sản phẩm nổi bật đang bán (is_featured + is_active), mới nhất trước,
     * kèm danh mục, size mặc định và ảnh chính để hiện thẻ sản phẩm.
     *
     * @return Collection<int, Product>
     */
    public function findFeaturedForSale(int $limit): Collection;

    /**
     * Sản phẩm đang bán thuộc một bộ sưu tập (theo slug, qua pivot category_product), sản phẩm nổi bật trước,
     * kèm danh mục, size mặc định và ảnh chính để hiện thẻ sản phẩm.
     *
     * @return Collection<int, Product>
     */
    public function findForSaleInCollection(string $collectionSlug): Collection;

    /**
     * Sản phẩm có kiểu hoa chính là danh mục này (products.category_id), theo tên, kèm dữ liệu dòng bảng.
     *
     * @return Collection<int, Product>
     */
    public function findByPrimaryCategory(Category $category): Collection;

    /**
     * Sản phẩm gắn vào bộ sưu tập qua pivot category_product, theo tên, kèm dữ liệu dòng bảng.
     *
     * @return Collection<int, Product>
     */
    public function findLinkedToCollection(Category $collection): Collection;

    /**
     * Sản phẩm chưa có trong bộ sưu tập để chọn thêm, tìm theo tên, theo tên.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateNotLinkedToCollection(Category $collection, ?string $search, int $perPage): LengthAwarePaginator;

    public function countAll(): int;

    public function countActive(): int;

    /**
     * Sản phẩm đang bán còn tồn kho không quá $threshold (gồm cả đã hết).
     */
    public function countActiveWithStockAtMost(int $threshold): int;

    /**
     * Sản phẩm đang bán cho trang "Tất cả hoa", lọc theo kiểu hoa, dịp gửi, sắc hoa và khoảng giá hiển thị
     * (giá size mặc định, chưa có size thì base_price), mới nhất trước, kèm dữ liệu thẻ sản phẩm.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateForCatalog(CatalogFilterData $filters, int $perPage): LengthAwarePaginator;

    /**
     * Sản phẩm đang bán theo slug cho trang chi tiết, kèm danh mục, size (theo giá) và toàn bộ ảnh (theo sort_order).
     */
    public function findForSaleBySlug(string $slug): ?Product;

    /**
     * Sản phẩm đang bán trong danh sách id, kèm size và ảnh chính — để kiểm tra lựa chọn, tính tiền và hiện giỏ hàng.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Product>
     */
    public function findForSaleWithSizes(array $ids): Collection;
}
