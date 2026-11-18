<?php

declare(strict_types=1);

namespace App\DTOs\Post;

/**
 * Bộ lọc danh sách bài admin. $published null = "Tất cả trạng thái".
 */
final readonly class AdminPostFilters
{
    public function __construct(
        public ?string $search = null,
        public ?bool $published = null,
    ) {}
}
