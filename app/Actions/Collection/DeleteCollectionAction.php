<?php

declare(strict_types=1);

namespace App\Actions\Collection;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

/**
 * Xóa bộ sưu tập kèm ảnh chính. Không xóa khi còn sản phẩm có kiểu hoa là bộ sưu tập này
 * (khóa ngoại products.category_id để cascade, xóa sẽ kéo theo sản phẩm); liên kết pivot tự xóa theo.
 */
final class DeleteCollectionAction
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private FilesystemFactory $storage,
    ) {}

    /**
     * @return bool false khi bộ sưu tập còn sản phẩm nên không xóa
     */
    public function execute(Category $collection): bool
    {
        if ($this->categories->hasPrimaryProducts($collection)) {
            return false;
        }

        $cover = $collection->cover_image;
        $this->categories->delete($collection);

        if ($cover !== null) {
            $this->storage->disk('public')->delete($cover);
        }

        return true;
    }
}
