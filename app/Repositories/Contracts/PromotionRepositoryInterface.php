<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Promotion;

interface PromotionRepositoryInterface
{
    /**
     * Mã ưu đãi đang bật (is_active) theo code.
     */
    public function findActiveByCode(string $code): ?Promotion;
}
