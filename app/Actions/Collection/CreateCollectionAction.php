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
 * Thêm bộ sưu tập; ảnh chính (nếu có) lưu vào storage/app/public/collections. Lưu DB lỗi thì xóa ảnh vừa ghi.
 */
final class CreateCollectionAction
{
    public const COVER_DIRECTORY = 'collections';

    public function __construct(
        private CategoryRepositoryInterface $categories,
        private FilesystemFactory $storage,
    ) {}

    public function execute(CollectionData $data): Category
    {
        $disk = $this->storage->disk('public');
        $cover = null;

        if ($data->cover !== null) {
            $cover = $disk->putFile(self::COVER_DIRECTORY, $data->cover)
                ?: throw new RuntimeException('Không lưu được ảnh chính bộ sưu tập.');
        }

        try {
            return $this->categories->create([
                'name' => $data->name,
                'slug' => $data->slug,
                'type' => $data->type,
                'description' => $data->description,
                'cover_image' => $cover,
                'is_active' => $data->isActive,
            ]);
        } catch (Throwable $exception) {
            if ($cover !== null) {
                $disk->delete($cover);
            }

            throw $exception;
        }
    }
}
