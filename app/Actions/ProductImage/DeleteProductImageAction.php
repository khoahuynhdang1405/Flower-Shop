<?php

declare(strict_types=1);

namespace App\Actions\ProductImage;

use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\DB;

/**
 * Xóa ảnh (bản ghi + file). Xóa đúng ảnh chính thì ảnh đứng đầu còn lại thành ảnh chính.
 * File chỉ bị xóa sau khi DB đã xóa xong, để lỗi DB không làm mất file của bản ghi còn tồn tại.
 */
final class DeleteProductImageAction
{
    public function __construct(
        private ProductImageRepositoryInterface $images,
        private SetPrimaryProductImageAction $setPrimary,
        private FilesystemFactory $storage,
    ) {}

    public function execute(ProductImage $image): void
    {
        DB::transaction(function () use ($image): void {
            $this->images->delete($image);

            if (! $image->is_primary) {
                return;
            }

            $nextImage = $this->images->findFirstByProduct($image->product_id);

            if ($nextImage !== null) {
                $this->setPrimary->execute($nextImage);
            }
        });

        $this->storage->disk('public')->delete($image->image_path);
    }
}
