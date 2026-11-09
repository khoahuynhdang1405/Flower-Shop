<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Cart\GetCartSummaryAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;

/**
 * GET /api/cart: giỏ hàng trong session kèm tạm tính, ưu đãi, phí giao, tổng.
 */
class CartController extends Controller
{
    public function show(GetCartSummaryAction $getSummary): CartResource
    {
        return new CartResource($getSummary->execute());
    }
}
