<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\DTOs\Post\PostData;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Tạo bài mới: slug lấy từ tiêu đề (cố định từ đây, sửa tiêu đề sau không đổi link), tác giả là admin đang đăng nhập,
 * ảnh bìa lưu vào storage/app/public/posts/thumbnails. Lưu DB lỗi thì xóa ảnh vừa ghi.
 */
final class CreatePostAction
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private FilesystemFactory $storage,
    ) {}

    public function execute(PostData $data, int $adminId): Post
    {
        $disk = $this->storage->disk('public');
        $thumbnail = null;

        if ($data->thumbnail !== null) {
            $thumbnail = $disk->putFile('posts/thumbnails', $data->thumbnail)
                ?: throw new RuntimeException('Không lưu được ảnh bìa bài viết.');
        }

        try {
            return $this->posts->create([
                'title' => $data->title,
                'slug' => Str::slug($data->title),
                'summary' => $data->summary,
                'content' => $data->content,
                'thumbnail' => $thumbnail,
                'admin_id' => $adminId,
                'is_published' => $data->isPublished,
            ]);
        } catch (Throwable $exception) {
            if ($thumbnail !== null) {
                $disk->delete($thumbnail);
            }

            throw $exception;
        }
    }
}
