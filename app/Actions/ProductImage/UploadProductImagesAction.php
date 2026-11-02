<?php

declare(strict_types=1);

namespace App\Actions\ProductImage;

use App\DTOs\ProductImage\UploadProductImagesData;
use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Lưu nhiều ảnh vào storage/app/public/products/{product_id}/ và tạo bản ghi product_images.
 * Sản phẩm chưa có ảnh chính thì ảnh đầu tiên của lượt này thành ảnh chính.
 * Lỗi giữa chừng: rollback DB và xóa các file đã ghi, không để lại file rác.
 */
final class UploadProductImagesAction
{
    public function __construct(
        private ProductImageRepositoryInterface $images,
        private FilesystemFactory $storage,
    ) {}

    /**
     * @return list<ProductImage>
     */
    public function execute(UploadProductImagesData $data): array
    {
        $disk = $this->storage->disk('public');
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($data, $disk, &$storedPaths): array {
                $needsPrimary = ! $this->images->hasPrimary($data->productId);
                $sortOrder = $this->images->nextSortOrder($data->productId);
                $created = [];

                foreach ($data->files as $file) {
                    $path = $disk->putFile("products/{$data->productId}", $file);

                    if ($path === false) {
                        throw new RuntimeException("Không lưu được ảnh cho sản phẩm #{$data->productId}.");
                    }

                    $storedPaths[] = $path;
                    $created[] = $this->images->create([
                        'product_id' => $data->productId,
                        'product_size_id' => $data->productSizeId,
                        'image_path' => $path,
                        'is_primary' => $needsPrimary,
                        'sort_order' => $sortOrder++,
                    ]);
                    $needsPrimary = false;
                }

                return $created;
            });
        } catch (Throwable $exception) {
            $disk->delete($storedPaths);

            throw $exception;
        }
    }
}
