<?php

declare(strict_types=1);

namespace App\DTOs\Post;

/**
 * Nội dung bài đã chuyển sang HTML an toàn, kèm mục lục từ các tiêu đề "##".
 */
final readonly class RenderedPostContent
{
    /**
     * @param  list<array{id: string, title: string}>  $headings
     */
    public function __construct(
        public string $html,
        public array $headings,
    ) {}
}
