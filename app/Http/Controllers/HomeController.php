<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Product\ListFeaturedProductsAction;
use Illuminate\View\View;

/**
 * Trang chủ: hero, 4 cam kết và "Những bó hoa được thương nhất" (sản phẩm is_featured).
 */
class HomeController extends Controller
{
    public function __invoke(ListFeaturedProductsAction $listFeaturedProducts): View
    {
        $featuredProducts = $listFeaturedProducts->execute();

        return view('home', [
            'featuredProducts' => $featuredProducts,
            // Chip lọc chỉ gồm các danh mục đang có sản phẩm nổi bật.
            'featuredCategories' => $featuredProducts->pluck('category')->unique('id')->values(),
        ]);
    }
}
