<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\ListHomeOccasionsAction;
use App\Actions\Post\ListLatestPostsAction;
use App\Actions\Product\ListFeaturedProductsAction;
use App\Enums\Season;
use Illuminate\View\View;

/**
 * Trang chủ: hero, 4 cam kết, dịp gửi thương, "Những bó hoa được thương nhất" (sản phẩm is_featured),
 * banner mùa hiện tại, giới thiệu tiệm, tư vấn riêng và "Ghé góc nhỏ của Lá" (3 bài mới nhất).
 */
class HomeController extends Controller
{
    public function __invoke(
        ListFeaturedProductsAction $listFeaturedProducts,
        ListLatestPostsAction $listLatestPosts,
        ListHomeOccasionsAction $listOccasions,
    ): View {
        $featuredProducts = $listFeaturedProducts->execute();
        $today = now();

        return view('home', [
            'occasions' => $listOccasions->execute(),
            'featuredProducts' => $featuredProducts,
            // Chip lọc chỉ gồm các danh mục đang có sản phẩm nổi bật.
            'featuredCategories' => $featuredProducts->pluck('category')->unique('id')->values(),
            'today' => $today,
            'currentSeason' => Season::fromMonth($today->month),
            'latestPosts' => $listLatestPosts->execute(),
        ]);
    }
}
