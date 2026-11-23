<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\ListSeasonCoversAction;
use App\Actions\Product\ListSeasonProductsAction;
use App\Enums\Season;
use Illuminate\View\View;

/**
 * Trang "Hoa theo mùa": mùa đang vào mùa và tháng trên tiêu đề lấy theo ngày hiện tại;
 * ảnh của từng mùa lấy từ ảnh chính bộ sưu tập Theo mùa tương ứng.
 */
class SeasonController extends Controller
{
    public function __invoke(ListSeasonCoversAction $listSeasonCovers, ListSeasonProductsAction $listSeasonProducts): View
    {
        $today = now();
        $currentSeason = Season::fromMonth($today->month);

        return view('seasons.index', [
            'today' => $today,
            'currentSeason' => $currentSeason,
            'seasonCovers' => $listSeasonCovers->execute(),
            'seasonProducts' => $listSeasonProducts->execute($currentSeason),
        ]);
    }
}
