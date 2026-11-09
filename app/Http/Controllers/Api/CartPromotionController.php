<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Cart\ApplyPromotionCodeAction;
use App\Actions\Cart\GetCartSummaryAction;
use App\Actions\Cart\RemovePromotionCodeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ApplyPromotionCodeRequest;
use App\Http\Resources\CartResource;

/**
 * /api/cart/promotions: áp / gỡ mã ưu đãi. Mã không có hoặc đang tắt → 422.
 * Giỏ chưa đủ giá trị tối thiểu thì mã vẫn được giữ (promotion.state = below_minimum) và tự áp khi đủ.
 */
class CartPromotionController extends Controller
{
    public function store(ApplyPromotionCodeRequest $request, ApplyPromotionCodeAction $applyCode, GetCartSummaryAction $getSummary): CartResource
    {
        $applyCode->execute($request->string('code')->toString());

        return new CartResource($getSummary->execute());
    }

    public function destroy(RemovePromotionCodeAction $removeCode, GetCartSummaryAction $getSummary): CartResource
    {
        $removeCode->execute();

        return new CartResource($getSummary->execute());
    }
}
