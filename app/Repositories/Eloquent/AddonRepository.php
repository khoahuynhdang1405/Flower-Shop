<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Addon;
use App\Repositories\Contracts\AddonRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

final class AddonRepository implements AddonRepositoryInterface
{
    public function findActiveByIds(array $ids): Collection
    {
        return Addon::query()
            ->whereKey($ids)
            ->where('is_active', true)
            ->get();
    }

    public function findAllActive(): Collection
    {
        return Addon::query()
            ->where('is_active', true)
            ->orderBy('is_free')
            ->orderBy('id')
            ->get();
    }
}
