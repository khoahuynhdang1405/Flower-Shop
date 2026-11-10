<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Season;
use Illuminate\View\View;

/**
 * Trang "Hoa theo mùa": mùa đang vào mùa và tháng trên tiêu đề lấy theo ngày hiện tại.
 */
class SeasonController extends Controller
{
    public function __invoke(): View
    {
        $today = now();

        return view('seasons.index', [
            'today' => $today,
            'currentSeason' => Season::fromMonth($today->month),
        ]);
    }
}
