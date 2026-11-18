<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Post\AdminPostFilters;
use App\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    /**
     * Bài đã đăng cho trang Góc nhỏ, mới nhất trước, kèm tác giả.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function paginatePublished(int $perPage): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?Post;

    /**
     * Các bài đã đăng mới nhất (khối "Ghé góc nhỏ của Lá" ở Trang chủ).
     *
     * @return Collection<int, Post>
     */
    public function findLatestPublished(int $limit): Collection;

    /**
     * Các bài đã đăng mới nhất, trừ bài đang đọc.
     *
     * @return Collection<int, Post>
     */
    public function findLatestPublishedExcept(int $postId, int $limit): Collection;

    /**
     * Danh sách bài cho Admin › Chuyện Hoa, mới cập nhật trước, kèm tác giả; tìm theo tiêu đề.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function paginateForAdmin(AdminPostFilters $filters, int $perPage): LengthAwarePaginator;

    public function countAll(): int;

    public function countPublished(): int;

    public function countUpdatedSince(CarbonInterface $since): int;

    /**
     * Bản nháp được sửa gần nhất (để nhắc "Tiếp tục bản nháp").
     */
    public function findLatestDraft(): ?Post;

    /**
     * @param  array{title: string, slug: string, summary: ?string, content: string, thumbnail: ?string, admin_id: int, is_published: bool}  $attributes
     */
    public function create(array $attributes): Post;

    /**
     * @param  array{title?: string, summary?: ?string, content?: string, thumbnail?: ?string, is_published?: bool}  $attributes
     */
    public function update(Post $post, array $attributes): void;

    public function delete(Post $post): void;
}
