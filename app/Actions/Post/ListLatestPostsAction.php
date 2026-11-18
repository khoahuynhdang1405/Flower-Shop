<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Khối "Ghé góc nhỏ của Lá" ở Trang chủ: 3 bài đã đăng mới nhất.
 */
final class ListLatestPostsAction
{
    public const LIMIT = 3;

    public function __construct(private PostRepositoryInterface $posts) {}

    /**
     * @return Collection<int, Post>
     */
    public function execute(): Collection
    {
        return $this->posts->findLatestPublished(self::LIMIT);
    }
}
