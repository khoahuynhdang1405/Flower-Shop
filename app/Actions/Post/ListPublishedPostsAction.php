<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Trang Góc nhỏ: bài đã đăng, mới nhất trước, 9 bài mỗi trang.
 */
final class ListPublishedPostsAction
{
    public const PER_PAGE = 9;

    public function __construct(private PostRepositoryInterface $posts) {}

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function execute(): LengthAwarePaginator
    {
        return $this->posts->paginatePublished(self::PER_PAGE);
    }
}
