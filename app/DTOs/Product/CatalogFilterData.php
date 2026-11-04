<?php

declare(strict_types=1);

namespace App\DTOs\Product;

use App\Enums\FlowerColor;
use App\Enums\PriceRange;

/**
 * Bộ lọc trang "Tất cả hoa". Trong một nhóm chọn nhiều = HOẶC, giữa các nhóm = VÀ; nhóm rỗng = không lọc.
 */
final readonly class CatalogFilterData
{
    /**
     * @param  list<string>  $typeSlugs  slug danh mục kiểu hoa (products.category_id)
     * @param  list<string>  $occasionSlugs  slug danh mục dịp gửi (pivot category_product)
     * @param  list<FlowerColor>  $colors  sắc hoa (product_colors)
     */
    public function __construct(
        public array $typeSlugs = [],
        public array $occasionSlugs = [],
        public ?PriceRange $priceRange = null,
        public array $colors = [],
    ) {}
}
