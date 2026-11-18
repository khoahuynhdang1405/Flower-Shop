<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

/**
 * Xóa bài và ảnh bìa của bài. Ảnh chèn trong nội dung được giữ lại (có thể đang dùng ở bài khác).
 */
final class DeletePostAction
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private FilesystemFactory $storage,
    ) {}

    public function execute(Post $post): void
    {
        $this->posts->delete($post);

        if ($post->thumbnail !== null) {
            $this->storage->disk('public')->delete($post->thumbnail);
        }
    }
}
