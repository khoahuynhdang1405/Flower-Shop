<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cart;

use App\Actions\Cart\ApplyPromotionCodeAction;
use App\Actions\Cart\RemovePromotionCodeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ApplyPromotionCodeRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Ô "Mã ưu đãi" ở giỏ: áp mã / bỏ mã.
 */
class CartPromotionController extends Controller
{
    public function store(ApplyPromotionCodeRequest $request, ApplyPromotionCodeAction $applyCode): RedirectResponse
    {
        $applyCode->execute($request->string('code')->toString());

        return to_route('cart.show');
    }

    public function destroy(RemovePromotionCodeAction $removeCode): RedirectResponse
    {
        $removeCode->execute();

        return to_route('cart.show');
    }
}
