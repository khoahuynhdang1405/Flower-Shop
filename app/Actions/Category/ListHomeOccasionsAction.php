<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Khối "Mỗi dịp, một cách gửi thương" ở Trang chủ: tối đa 4 dịp gửi thương đang hiển thị, theo thứ tự tạo.
 */
final class ListHomeOccasionsAction
{
    public const LIMIT = 4;

    public function __construct(private CategoryRepositoryInterface $categories) {}

    /**
     * @return Collection<int, Category>
     */
    public function execute(): Collection
    {
        return $this->categories->findActiveOccasions()->take(self::LIMIT);
    }
}
