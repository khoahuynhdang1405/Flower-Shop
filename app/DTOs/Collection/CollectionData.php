<?php

declare(strict_types=1);

namespace App\DTOs\Collection;

use App\Enums\CategoryType;
use Illuminate\Http\UploadedFile;

/**
 * Dữ liệu form Thêm/Sửa bộ sưu tập. $cover là ảnh chính mới (nếu chọn); $removeCover gỡ ảnh chính hiện có.
 */
final readonly class CollectionData
{
    public function __construct(
        public string $name,
        public string $slug,
        public CategoryType $type,
        public ?string $description,
        public bool $isActive,
        public ?UploadedFile $cover = null,
        public bool $removeCover = false,
    ) {}
}
