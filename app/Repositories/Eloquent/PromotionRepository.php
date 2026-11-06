<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Promotion;
use App\Repositories\Contracts\PromotionRepositoryInterface;

final class PromotionRepository implements PromotionRepositoryInterface
{
    public function findActiveByCode(string $code): ?Promotion
    {
        return Promotion::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();
    }
}
