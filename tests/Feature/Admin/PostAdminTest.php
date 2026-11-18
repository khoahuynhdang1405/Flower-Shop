<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Post;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostAdminTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private FilesystemAdapter $disk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
        $this->disk = Storage::fake('public');
        $this->admin = Admin::factory()->create(['name' => 'Mai']);
    }

    public function test_guests_and_customers_cannot_manage_posts(): void
    {
        $post = Post::factory()->create();

        $this->get(route('admin.posts'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get(route('admin.posts.create'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.posts.store'), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.posts.destroy', $post))->assertRedirect(route('admin.login'));
        $this->post(route('admin.posts.preview'), ['content' => 'x'])->assertRedirect(route('admin.login'));
    }

    public function test_index_shows_stats_filters_and_latest_draft_reminder(): void
    {
        Post::factory()->for($this->admin, 'author')->create(['title' => 'Để hoa tươi lâu hơn, thương lâu hơn']);
        Post::factory()->for($this->admin, 'author')->draft()->create(['title' => 'Chọn hoa gửi mẹ ngày 20/10']);
        Post::factory()->create(['title' => 'Bài cũ', 'updated_at' => now()->subMonth()]);

        $this->asAdmin()
            ->get(route('admin.posts'))
            ->assertOk()
            ->assertSee('<span aria-current="page">Chuyện Hoa</span>', false)
            ->assertSeeTextInOrder(['Tổng bài viết', '03', 'Đã đăng', '02', 'Bản nháp', '01', 'Cập nhật tuần này', '02'])
            ->assertSeeTextInOrder(['Để hoa tươi lâu hơn, thương lâu hơn', 'Mai', 'Đã đăng'])
            ->assertSeeText('Hoàn thiện bài “Chọn hoa gửi mẹ ngày 20/10” rồi đăng lên Góc nhỏ nhé.');

        $this->get(route('admin.posts', ['status' => 'draft', 'q' => 'mẹ']))
            ->assertOk()
            ->assertSeeText('Chọn hoa gửi mẹ ngày 20/10')
            ->assertDontSeeText('Để hoa tươi lâu hơn, thương lâu hơn');
    }

    public function test_admin_creates_a_published_post_with_thumbnail(): void
    {
        $this->asAdmin()->get(route('admin.posts.create'))->assertOk()->assertSee('data-post-editor', false);

        $response = $this->post(route('admin.posts.store'), [
            'title' => '  Để hoa tươi lâu hơn, thương lâu hơn ',
            'summary' => 'Một bó hoa đẹp không chỉ trong ngày đầu.',
            'content' => "Mở đầu.\n\n## Nước mới cho một ngày mới",
            'is_published' => '1',
            'thumbnail' => UploadedFile::fake()->create('bia.jpg', 300, 'image/jpeg'),
        ]);

        $post = Post::sole();
        $response->assertRedirect(route('admin.posts.edit', $post))
            ->assertSessionHas('status', 'Đã lưu và đăng bài “Để hoa tươi lâu hơn, thương lâu hơn”.');

        $this->assertSame('de-hoa-tuoi-lau-hon-thuong-lau-hon', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertTrue($post->author->is($this->admin));
        $this->assertStringStartsWith('posts/thumbnails/', $post->thumbnail);
        $this->disk->assertExists($post->thumbnail);
    }

    public function test_create_validates_input_and_duplicate_titles(): void
    {
        Post::factory()->create(['title' => 'Để hoa tươi lâu hơn', 'slug' => 'de-hoa-tuoi-lau-hon']);

        $this->asAdmin()
            ->post(route('admin.posts.store'), ['title' => '', 'content' => '', 'thumbnail' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors([
                'title' => __('admin.posts.form.title_required'),
                'content' => __('admin.posts.form.content_required'),
                'thumbnail' => __('admin.posts.form.image_type'),
            ]);

        $this->post(route('admin.posts.store'), ['title' => 'Để hoa TƯƠI lâu hơn', 'content' => 'Nội dung'])
            ->assertSessionHasErrors(['title' => __('admin.posts.form.title_taken')])
            ->assertSessionDoesntHaveErrors('slug');

        $this->assertSame(1, Post::count());
    }

    public function test_admin_updates_a_post_keeping_slug_and_replacing_thumbnail(): void
    {
        $this->disk->put('posts/thumbnails/cu.jpg', 'x');
        $post = Post::factory()->for($this->admin, 'author')->create([
            'title' => 'Tiêu đề cũ', 'slug' => 'tieu-de-cu', 'thumbnail' => 'posts/thumbnails/cu.jpg',
        ]);

        $this->asAdmin()->get(route('admin.posts.edit', $post))->assertOk()->assertSee('value="Tiêu đề cũ"', false);

        $this->put(route('admin.posts.update', $post), [
            'title' => 'Tiêu đề mới',
            'content' => 'Nội dung mới',
            'is_published' => '0',
            'thumbnail' => UploadedFile::fake()->create('moi.webp', 200, 'image/webp'),
        ])->assertRedirect(route('admin.posts.edit', $post))->assertSessionHas('status', 'Đã lưu bản nháp “Tiêu đề mới”.');

        $post->refresh();
        $this->assertSame('Tiêu đề mới', $post->title);
        $this->assertSame('tieu-de-cu', $post->slug);
        $this->assertFalse($post->is_published);
        $this->disk->assertMissing('posts/thumbnails/cu.jpg');
        $this->disk->assertExists($post->thumbnail);
    }

    public function test_admin_can_remove_thumbnail(): void
    {
        $this->disk->put('posts/thumbnails/cu.jpg', 'x');
        $post = Post::factory()->create(['thumbnail' => 'posts/thumbnails/cu.jpg']);

        $this->asAdmin()->put(route('admin.posts.update', $post), [
            'title' => $post->title, 'content' => $post->content, 'is_published' => '1', 'remove_thumbnail' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($post->refresh()->thumbnail);
        $this->disk->assertMissing('posts/thumbnails/cu.jpg');
    }

    public function test_admin_deletes_a_post_and_its_thumbnail(): void
    {
        $this->disk->put('posts/thumbnails/cu.jpg', 'x');
        $post = Post::factory()->create(['title' => 'Bài cần xóa', 'thumbnail' => 'posts/thumbnails/cu.jpg']);

        $this->asAdmin()
            ->delete(route('admin.posts.destroy', $post))
            ->assertRedirect(route('admin.posts'))
            ->assertSessionHas('status', 'Đã xóa bài “Bài cần xóa”.');

        $this->assertModelMissing($post);
        $this->disk->assertMissing('posts/thumbnails/cu.jpg');
    }

    public function test_preview_returns_safe_html(): void
    {
        $this->asAdmin()
            ->postJson(route('admin.posts.preview'), ['content' => "## Mục một\n\n<script>alert(1)</script>\n\n**đậm**"])
            ->assertOk()
            ->assertJsonPath('html', fn (string $html): bool => str_contains($html, '<h2 id="muc-1">Mục một</h2>')
                && str_contains($html, '<strong>đậm</strong>')
                && ! str_contains($html, '<script>'));
    }

    public function test_content_image_upload_returns_url_and_validates_type(): void
    {
        $response = $this->asAdmin()
            ->postJson(route('admin.posts.images'), ['image' => UploadedFile::fake()->create('hoa.png', 300, 'image/png')])
            ->assertCreated();

        $this->assertStringContainsString('/storage/posts/content/', $response->json('url'));
        $this->assertCount(1, $this->disk->files('posts/content'));

        $this->postJson(route('admin.posts.images'), ['image' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image' => __('admin.posts.form.image_type')]);
    }

    public function test_pages_render_in_english(): void
    {
        app()->setLocale('en');
        $post = Post::factory()->create();

        $this->asAdmin()->get(route('admin.posts'))->assertOk()->assertSeeText('The little corner, flower stories');
        $this->get(route('admin.posts.edit', $post))->assertOk()->assertSeeText('Publish to the little corner');
    }

    private function asAdmin(): static
    {
        return $this->actingAs($this->admin, 'admin');
    }
}
