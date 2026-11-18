<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JournalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_index_lists_only_published_posts_newest_first(): void
    {
        Post::factory()->create(['title' => 'Mỗi màu hoa hồng, một lời muốn nói', 'created_at' => now()->subDays(2)]);
        Post::factory()->create(['title' => 'Để hoa tươi lâu hơn, thương lâu hơn', 'created_at' => now()->subDay()]);
        Post::factory()->draft()->create(['title' => 'Chọn hoa gửi mẹ ngày 20/10']);

        $response = $this->get(route('posts.index'))
            ->assertOk()
            ->assertSeeText('Ghé góc nhỏ của Lá')
            ->assertSeeTextInOrder(['Để hoa tươi lâu hơn, thương lâu hơn', 'Mỗi màu hoa hồng, một lời muốn nói'])
            ->assertDontSeeText('Chọn hoa gửi mẹ ngày 20/10');

        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('posts.index'), '/').'"\s+aria-current="page"/', $response->getContent());
    }

    public function test_index_paginates_nine_posts_per_page(): void
    {
        Post::factory()->count(10)->create();

        $this->get(route('posts.index', ['page' => 2]))->assertOk()->assertSee('aria-current="page"', false);
        $this->assertSame(1, substr_count($this->get(route('posts.index', ['page' => 2]))->getContent(), 'Đọc câu chuyện'));
    }

    public function test_index_shows_a_gentle_note_when_there_are_no_posts(): void
    {
        Post::factory()->draft()->create();

        $this->get(route('posts.index'))->assertOk()->assertSeeText('Góc nhỏ đang chờ câu chuyện đầu tiên');
    }

    public function test_show_renders_markdown_with_table_of_contents_and_related_posts(): void
    {
        $author = Admin::factory()->create(['name' => 'Khoa']);
        $post = Post::factory()->for($author, 'author')->create([
            'title' => 'Để hoa tươi lâu hơn, thương lâu hơn',
            'summary' => 'Một bó hoa đẹp không chỉ trong ngày đầu.',
            'content' => "Mở đầu.\n\n## 01. Cho hoa một khởi đầu thật sạch\n\n**Rửa bình** thật kỹ.\n\n> Chăm hoa là để mỗi ngày đều đẹp.\n\n## 02. Nước mới cho một ngày mới\n\nThay nước.",
            'created_at' => Carbon::parse('2026-10-02'),
        ]);
        Post::factory()->count(4)->create();
        Post::factory()->draft()->create(['title' => 'Bản nháp bí mật']);

        $response = $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSeeText('Một bó hoa đẹp không chỉ trong ngày đầu.')
            ->assertSeeText('Bởi Khoa · Người làm hoa ở Lá')
            ->assertSeeText('02/10/2026')
            ->assertSeeText('1 phút đọc')
            ->assertSee('<h2 id="muc-1">01. Cho hoa một khởi đầu thật sạch</h2>', false)
            ->assertSee('<strong>Rửa bình</strong>', false)
            ->assertSee('<blockquote>', false)
            ->assertSeeTextInOrder(['Trong câu chuyện này', '01. Cho hoa một khởi đầu thật sạch', '02. Nước mới cho một ngày mới'])
            ->assertSee('href="#muc-2"', false)
            ->assertSeeText('Những chuyện hoa khác ở Lá')
            ->assertDontSeeText('Bản nháp bí mật');

        $this->assertSame(3, substr_count($response->getContent(), 'Đọc câu chuyện'));
    }

    public function test_show_strips_raw_html_and_unsafe_links(): void
    {
        $post = Post::factory()->create([
            'content' => "Xin chào.\n\n<script>alert('xss')</script>\n\n[bấm vào đây](javascript:alert(1))",
        ]);

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertDontSee("<script>alert('xss')</script>", false)
            ->assertDontSee('javascript:alert(1)', false);
    }

    public function test_drafts_and_unknown_posts_are_not_found(): void
    {
        $draft = Post::factory()->draft()->create();

        $this->get(route('posts.show', $draft->slug))->assertNotFound();
        $this->get(route('posts.show', 'khong-co-bai-nay'))->assertNotFound();
    }

    public function test_pages_render_in_english(): void
    {
        app()->setLocale('en');
        $post = Post::factory()->create();

        $this->get(route('posts.index'))->assertOk()->assertSeeText('Visit Lá’s little corner');
        $this->get(route('posts.show', $post->slug))->assertOk()->assertSeeText('1 min read')->assertSeeText('In this story');
    }
}
