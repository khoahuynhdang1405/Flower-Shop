<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Addon;
use Illuminate\Database\Eloquent\Collection;

interface AddonRepositoryInterface
{
    /**
     * Quà kèm khách còn chọn được (is_active) trong danh sách id.
     *
     * @param  list<int>  $ids
     * @return Collection<int, Addon>
     */
    public function findActiveByIds(array $ids): Collection;

    /**
     * Toàn bộ quà kèm khách còn chọn được, món tính tiền trước, thiệp miễn phí cuối.
     *
     * @return Collection<int, Addon>
     */
    public function findAllActive(): Collection;
}
