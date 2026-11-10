<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Checkout\GetCheckoutSummaryAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Trang "Gửi hoa, gửi yêu thương" (bước 2/3). Giỏ trống thì quay về giỏ hàng.
 */
class CheckoutController extends Controller
{
    public function __invoke(Request $request, GetCheckoutSummaryAction $getSummary): View|RedirectResponse
    {
        $summary = $getSummary->execute();

        if ($summary->lines->isEmpty()) {
            return to_route('cart.show');
        }

        return view('checkout.show', [
            'summary' => $summary,
            'customer' => $request->user(),
            'today' => now(),
        ]);
    }
}
