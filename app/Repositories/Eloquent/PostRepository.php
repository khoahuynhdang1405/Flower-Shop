<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Post\AdminPostFilters;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class PostRepository implements PostRepositoryInterface
{
    public function paginatePublished(int $perPage): LengthAwarePaginator
    {
        return Post::query()
            ->where('is_published', true)
            ->with('author')
            ->latest()
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()->where('slug', $slug)->where('is_published', true)->with('author')->first();
    }

    public function findLatestPublishedExcept(int $postId, int $limit): Collection
    {
        return Post::query()
            ->where('is_published', true)
            ->whereKeyNot($postId)
            ->latest()
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function paginateForAdmin(AdminPostFilters $filters, int $perPage): LengthAwarePaginator
    {
        return Post::query()
            ->when($filters->search, fn (Builder $query, string $search) => $query->whereLike('title', '%'.$search.'%'))
            ->when($filters->published !== null, fn (Builder $query) => $query->where('is_published', $filters->published))
            ->with('author')
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countAll(): int
    {
        return Post::query()->count();
    }

    public function countPublished(): int
    {
        return Post::query()->where('is_published', true)->count();
    }

    public function countUpdatedSince(CarbonInterface $since): int
    {
        return Post::query()->where('updated_at', '>=', $since)->count();
    }

    public function findLatestDraft(): ?Post
    {
        return Post::query()->where('is_published', false)->latest('updated_at')->orderByDesc('id')->first();
    }

    public function create(array $attributes): Post
    {
        return Post::create($attributes);
    }

    public function update(Post $post, array $attributes): void
    {
        $post->update($attributes);
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }
}
