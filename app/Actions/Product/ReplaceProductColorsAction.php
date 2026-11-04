<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Enums\FlowerColor;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Lưu sắc hoa admin chọn cho sản phẩm (thay toàn bộ sắc cũ), dùng cho bộ lọc "Sắc hoa bạn thích".
 */
final class ReplaceProductColorsAction
{
    public function __construct(private ProductRepositoryInterface $products) {}

    /**
     * @param  list<FlowerColor>  $colors
     */
    public function execute(Product $product, array $colors): void
    {
        DB::transaction(fn () => $this->products->replaceColors($product, array_values(array_unique($colors, SORT_REGULAR))));
    }
}
