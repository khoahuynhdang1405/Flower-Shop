<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Trang chi tiết sản phẩm: sản phẩm đang bán theo slug, kèm size và ảnh.
 * Không có hoặc đã ngừng bán thì báo không tìm thấy (404).
 */
final class ShowProductDetailAction
{
    public function __construct(private ProductRepositoryInterface $products) {}

    /**
     * @throws ModelNotFoundException<Product>
     */
    public function execute(string $slug): Product
    {
        return $this->products->findForSaleBySlug($slug)
            ?? throw (new ModelNotFoundException)->setModel(Product::class, [$slug]);
    }
}
