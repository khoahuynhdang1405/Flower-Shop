<?php

declare(strict_types=1);

namespace App\Actions\Collection;

use App\DTOs\Collection\CollectionData;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use RuntimeException;
use Throwable;

/**
 * Sửa bộ sưu tập. Ảnh chính mới thay ảnh cũ (ảnh cũ chỉ xóa sau khi DB lưu xong); $removeCover gỡ ảnh chính hiện có.
 */
final class UpdateCollectionAction
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private FilesystemFactory $storage,
    ) {}

    public function execute(Category $collection, CollectionData $data): void
    {
        $disk = $this->storage->disk('public');
        $oldCover = $collection->cover_image;
        $cover = $data->removeCover ? null : $oldCover;

        if ($data->cover !== null) {
            $cover = $disk->putFile(CreateCollectionAction::COVER_DIRECTORY, $data->cover)
                ?: throw new RuntimeException('Không lưu được ảnh chính bộ sưu tập.');
        }

        try {
            $this->categories->update($collection, [
                'name' => $data->name,
                'slug' => $data->slug,
                'type' => $data->type,
                'description' => $data->description,
                'cover_image' => $cover,
                'is_active' => $data->isActive,
            ]);
        } catch (Throwable $exception) {
            if ($cover !== null && $cover !== $oldCover) {
                $disk->delete($cover);
            }

            throw $exception;
        }

        if ($oldCover !== null && $oldCover !== $cover) {
            $disk->delete($oldCover);
        }
    }
}
