<?php

declare(strict_types=1);

namespace App\Actions\Post;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Lưu ảnh chèn trong nội dung bài vào storage/app/public/posts/content và trả về URL để chèn vào Markdown.
 */
final class UploadPostImageAction
{
    public function __construct(private FilesystemFactory $storage) {}

    public function execute(File $image): string
    {
        $disk = $this->storage->disk('public');
        $path = $disk->putFile('posts/content', $image)
            ?: throw new RuntimeException('Không lưu được ảnh trong bài viết.');

        return $disk->url($path);
    }
}
