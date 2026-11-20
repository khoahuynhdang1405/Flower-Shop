<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Category\ListSeasonCoversAction;
use App\Enums\Season;
use Illuminate\View\View;

/**
 * Trang "Hoa theo mùa": mùa đang vào mùa và tháng trên tiêu đề lấy theo ngày hiện tại;
 * ảnh của từng mùa lấy từ ảnh chính bộ sưu tập Theo mùa tương ứng.
 */
class SeasonController extends Controller
{
    public function __invoke(ListSeasonCoversAction $listSeasonCovers): View
    {
        $today = now();

        return view('seasons.index', [
            'today' => $today,
            'currentSeason' => Season::fromMonth($today->month),
            'seasonCovers' => $listSeasonCovers->execute(),
        ]);
    }
}
