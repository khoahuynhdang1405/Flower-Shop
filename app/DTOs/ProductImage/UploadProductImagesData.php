<?php

declare(strict_types=1);

namespace App\DTOs\ProductImage;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Một lượt upload: nhiều ảnh cho cùng một sản phẩm, cùng gắn vào một size (null = ảnh chung).
 */
final readonly class UploadProductImagesData
{
    /**
     * @param  list<File>  $files
     */
    public function __construct(
        public int $productId,
        public ?int $productSizeId,
        public array $files,
    ) {}
}
