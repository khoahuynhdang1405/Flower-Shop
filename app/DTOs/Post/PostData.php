<?php

declare(strict_types=1);

namespace App\DTOs\Post;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Nội dung một bài khi tạo / sửa. $thumbnail null = giữ ảnh bìa hiện có (khi sửa) hoặc không có ảnh (khi tạo);
 * $removeThumbnail = gỡ ảnh bìa hiện có.
 */
final readonly class PostData
{
    public function __construct(
        public string $title,
        public ?string $summary,
        public string $content,
        public bool $isPublished,
        public ?File $thumbnail = null,
        public bool $removeThumbnail = false,
    ) {}
}
