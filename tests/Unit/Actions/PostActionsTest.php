<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\ListAdminPostsAction;
use App\Actions\Post\RenderPostContentAction;
use App\Actions\Post\ShowPublishedPostAction;
use App\Actions\Post\UpdatePostAction;
use App\DTOs\Post\AdminPostFilters;
use App\DTOs\Post\PostData;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

/**
 * Nghiệp vụ bài Góc nhỏ, với repository giả (không chạm database).
 */
class PostActionsTest extends TestCase
{
    private PostRepositoryInterface&MockInterface $posts;

    private FilesystemAdapter $disk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->posts = Mockery::mock(PostRepositoryInterface::class);
        $this->disk = Storage::fake('public');
    }

    public function test_render_adds_heading_ids_and_turns_lone_images_into_figures(): void
    {
        $content = (new RenderPostContentAction)->execute("## Mục *một*\n\n![Góc nhỏ dễ chịu](/storage/posts/content/a.jpg)\n\n## Mục hai");

        $this->assertSame([['id' => 'muc-1', 'title' => 'Mục một'], ['id' => 'muc-2', 'title' => 'Mục hai']], $content->headings);
        $this->assertStringContainsString('<figure><img src="/storage/posts/content/a.jpg" alt="Góc nhỏ dễ chịu" loading="lazy"><figcaption>Góc nhỏ dễ chịu</figcaption></figure>', $content->html);
    }

    public function test_show_throws_not_found_for_missing_posts(): void
    {
        $this->posts->expects('findPublishedBySlug')->with('khong-co')->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        (new ShowPublishedPostAction($this->posts, new RenderPostContentAction))->execute('khong-co');
    }

    public function test_admin_list_counts_drafts_and_recent_updates(): void
    {
        $now = CarbonImmutable::parse('2026-10-07 09:00');
        $page = Mockery::mock(LengthAwarePaginator::class);
        $draft = new Post;
        $this->posts->expects('paginateForAdmin')->with(Mockery::type(AdminPostFilters::class), 6)->andReturn($page);
        $this->posts->expects('countAll')->andReturn(18);
        $this->posts->expects('countPublished')->andReturn(14);
        $this->posts->expects('countUpdatedSince')->withArgs(fn ($since): bool => $since->equalTo($now->subDays(7)))->andReturn(5);
        $this->posts->expects('findLatestDraft')->andReturn($draft);

        $result = (new ListAdminPostsAction($this->posts))->execute(new AdminPostFilters, $now);

        $this->assertSame(['total' => 18, 'published' => 14, 'drafts' => 4, 'updatedThisWeek' => 5], $result['stats']);
        $this->assertSame($draft, $result['latestDraft']);
    }

    public function test_create_stores_thumbnail_and_slug_from_title(): void
    {
        $this->posts->expects('create')->with(Mockery::on(fn (array $attributes): bool => $attributes['slug'] === 'de-hoa-tuoi-lau-hon'
            && $attributes['admin_id'] === 3
            && str_starts_with($attributes['thumbnail'], 'posts/thumbnails/')))->andReturn(new Post);

        $this->createAction()->execute($this->data(thumbnail: UploadedFile::fake()->create('bia.jpg', 100, 'image/jpeg')), 3);

        $this->assertCount(1, $this->disk->files('posts/thumbnails'));
    }

    public function test_create_removes_stored_thumbnail_when_saving_fails(): void
    {
        $this->posts->expects('create')->andThrow(new RuntimeException('DB lỗi'));

        try {
            $this->createAction()->execute($this->data(thumbnail: UploadedFile::fake()->create('bia.jpg', 100, 'image/jpeg')), 3);
            $this->fail('Action phải ném lại lỗi.');
        } catch (RuntimeException) {
            $this->assertEmpty($this->disk->allFiles());
        }
    }

    public function test_update_keeps_thumbnail_when_no_new_one_is_given(): void
    {
        $this->disk->put('posts/thumbnails/cu.jpg', 'x');
        $post = (new Post)->forceFill(['id' => 1, 'thumbnail' => 'posts/thumbnails/cu.jpg']);
        $this->posts->expects('update')->with($post, Mockery::on(fn (array $attributes): bool => $attributes['thumbnail'] === 'posts/thumbnails/cu.jpg'
            && ! array_key_exists('slug', $attributes)));

        (new UpdatePostAction($this->posts, $this->app->make(FilesystemFactory::class)))->execute($post, $this->data());

        $this->disk->assertExists('posts/thumbnails/cu.jpg');
    }

    public function test_delete_removes_record_then_thumbnail(): void
    {
        $this->disk->put('posts/thumbnails/cu.jpg', 'x');
        $post = (new Post)->forceFill(['id' => 1, 'thumbnail' => 'posts/thumbnails/cu.jpg']);
        $this->posts->expects('delete')->with($post);

        (new DeletePostAction($this->posts, $this->app->make(FilesystemFactory::class)))->execute($post);

        $this->disk->assertMissing('posts/thumbnails/cu.jpg');
    }

    public function test_show_returns_rendered_content_and_related_posts(): void
    {
        $post = (new Post)->forceFill(['id' => 4, 'content' => '## Mục một']);
        $related = new Collection([new Post]);
        $this->posts->expects('findPublishedBySlug')->with('de-hoa')->andReturn($post);
        $this->posts->expects('findLatestPublishedExcept')->with(4, 3)->andReturn($related);

        $result = (new ShowPublishedPostAction($this->posts, new RenderPostContentAction))->execute('de-hoa');

        $this->assertSame($post, $result['post']);
        $this->assertSame($related, $result['related']);
        $this->assertSame('muc-1', $result['content']->headings[0]['id']);
    }

    private function createAction(): CreatePostAction
    {
        return new CreatePostAction($this->posts, $this->app->make(FilesystemFactory::class));
    }

    private function data(?UploadedFile $thumbnail = null): PostData
    {
        return new PostData(
            title: 'Để hoa tươi lâu hơn',
            summary: null,
            content: 'Nội dung',
            isPublished: true,
            thumbnail: $thumbnail,
        );
    }
}
