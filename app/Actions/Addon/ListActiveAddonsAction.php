<?php

declare(strict_types=1);

namespace App\Actions\Addon;

use App\Models\Addon;
use App\Repositories\Contracts\AddonRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Khối "Thêm một chút thương yêu" ở trang chi tiết: các quà kèm khách còn chọn được.
 */
final class ListActiveAddonsAction
{
    public function __construct(private AddonRepositoryInterface $addons) {}

    /**
     * @return Collection<int, Addon>
     */
    public function execute(): Collection
    {
        return $this->addons->findAllActive();
    }
}
