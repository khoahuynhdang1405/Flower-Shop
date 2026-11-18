<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\DTOs\Post\RenderedPostContent;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Trang bài viết: bài đã đăng theo slug (bản nháp → 404), nội dung đã chuyển HTML và 3 bài khác để đọc thêm.
 */
final class ShowPublishedPostAction
{
    public const RELATED = 3;

    public function __construct(
        private PostRepositoryInterface $posts,
        private RenderPostContentAction $renderContent,
    ) {}

    /**
     * @return array{post: Post, content: RenderedPostContent, related: Collection<int, Post>}
     */
    public function execute(string $slug): array
    {
        $post = $this->posts->findPublishedBySlug($slug)
            ?? throw (new ModelNotFoundException)->setModel(Post::class, [$slug]);

        return [
            'post' => $post,
            'content' => $this->renderContent->execute($post->content),
            'related' => $this->posts->findLatestPublishedExcept($post->id, self::RELATED),
        ];
    }
}
