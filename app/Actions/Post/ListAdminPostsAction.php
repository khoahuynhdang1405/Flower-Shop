<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\DTOs\Post\AdminPostFilters;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Dữ liệu trang Admin › Chuyện Hoa: trang bài theo bộ lọc, 4 thẻ số liệu và bản nháp sửa gần nhất để nhắc.
 */
final class ListAdminPostsAction
{
    public const PER_PAGE = 6;

    public function __construct(private PostRepositoryInterface $posts) {}

    /**
     * @return array{
     *     posts: LengthAwarePaginator<int, Post>,
     *     stats: array{total: int, published: int, drafts: int, updatedThisWeek: int},
     *     latestDraft: ?Post
     * }
     */
    public function execute(AdminPostFilters $filters, CarbonImmutable $now): array
    {
        $total = $this->posts->countAll();
        $published = $this->posts->countPublished();

        return [
            'posts' => $this->posts->paginateForAdmin($filters, self::PER_PAGE),
            'stats' => [
                'total' => $total,
                'published' => $published,
                'drafts' => $total - $published,
                'updatedThisWeek' => $this->posts->countUpdatedSince($now->subDays(7)),
            ],
            'latestDraft' => $this->posts->findLatestDraft(),
        ];
    }
}
