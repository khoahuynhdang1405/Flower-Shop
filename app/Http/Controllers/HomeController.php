<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Post\ListLatestPostsAction;
use App\Actions\Product\ListFeaturedProductsAction;
use Illuminate\View\View;

/**
 * Trang chủ: hero, 4 cam kết, "Những bó hoa được thương nhất" (sản phẩm is_featured)
 * và "Ghé góc nhỏ của Lá" (3 bài mới nhất).
 */
class HomeController extends Controller
{
    public function __invoke(ListFeaturedProductsAction $listFeaturedProducts, ListLatestPostsAction $listLatestPosts): View
    {
        $featuredProducts = $listFeaturedProducts->execute();

        return view('home', [
            'featuredProducts' => $featuredProducts,
            // Chip lọc chỉ gồm các danh mục đang có sản phẩm nổi bật.
            'featuredCategories' => $featuredProducts->pluck('category')->unique('id')->values(),
            'latestPosts' => $listLatestPosts->execute(),
        ]);
    }
}
