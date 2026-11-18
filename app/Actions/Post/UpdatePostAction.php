<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\DTOs\Post\PostData;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use RuntimeException;
use Throwable;

/**
 * Sửa bài: giữ slug và tác giả. Ảnh bìa mới thay ảnh cũ (ảnh cũ chỉ xóa sau khi DB lưu xong);
 * $removeThumbnail gỡ ảnh bìa hiện có.
 */
final class UpdatePostAction
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private FilesystemFactory $storage,
    ) {}

    public function execute(Post $post, PostData $data): void
    {
        $disk = $this->storage->disk('public');
        $oldThumbnail = $post->thumbnail;
        $thumbnail = $data->removeThumbnail ? null : $oldThumbnail;

        if ($data->thumbnail !== null) {
            $thumbnail = $disk->putFile('posts/thumbnails', $data->thumbnail)
                ?: throw new RuntimeException('Không lưu được ảnh bìa bài viết.');
        }

        try {
            $this->posts->update($post, [
                'title' => $data->title,
                'summary' => $data->summary,
                'content' => $data->content,
                'thumbnail' => $thumbnail,
                'is_published' => $data->isPublished,
            ]);
        } catch (Throwable $exception) {
            if ($thumbnail !== null && $thumbnail !== $oldThumbnail) {
                $disk->delete($thumbnail);
            }

            throw $exception;
        }

        if ($oldThumbnail !== null && $oldThumbnail !== $thumbnail) {
            $disk->delete($oldThumbnail);
        }
    }
}
