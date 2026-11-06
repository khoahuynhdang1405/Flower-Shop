<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Actions\Cart\GetCartSummaryAction;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Trang "Giỏ hoa của bạn".
 */
class CartController extends Controller
{
    public function show(GetCartSummaryAction $getSummary): View
    {
        return view('cart.show', ['summary' => $getSummary->execute()]);
    }
}
