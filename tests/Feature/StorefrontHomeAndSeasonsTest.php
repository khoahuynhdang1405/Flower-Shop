<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Category\ListHomeOccasionsAction;
use App\Actions\Product\ListFeaturedProductsAction;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StorefrontHomeAndSeasonsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_home_page_shows_hero_and_promises(): void
    {
        app()->setLocale('vi');

        $response = $this->get(route('home'));

        $this->assertMatchesRegularExpression('/href="'.preg_quote(url('/'), '/').'"\s+aria-current="page"/', $response->getContent());

        $response
            ->assertOk()
            ->assertSeeText('Gửi một đóa hoa,')
            ->assertSeeText('Tươi từ vườn, đẹp đến tay')
            ->assertSee('href="'.route('seasons').'"', false);
    }

    public function test_home_page_lists_only_featured_products_on_sale(): void
    {
        app()->setLocale('vi');
        $bouquets = Category::factory()->create(['name' => 'Bó hoa', 'slug' => 'bo-hoa']);
        $baskets = Category::factory()->create(['name' => 'Giỏ & hộp hoa', 'slug' => 'gio-hop-hoa']);

        $whisper = Product::factory()->featured()->for($bouquets)->create(['name' => 'Lời thì thầm', 'base_price' => 480000]);
        ProductSize::factory()->for($whisper)->create(['size_name' => 'Vừa', 'price' => 680000, 'is_default' => true]);
        ProductImage::factory()->for($whisper)->primary()->create(['image_path' => 'products/1/loi-thi-tham.jpg']);
        Product::factory()->featured()->for($baskets)->create(['name' => 'Bình yên xanh', 'base_price' => 750000]);
        Product::factory()->for($bouquets)->create(['name' => 'Hồng phấn ban mai']);
        Product::factory()->featured()->hidden()->for($bouquets)->create(['name' => 'Cúc họa mi tháng mười']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Những bó hoa được thương nhất')
            ->assertSeeTextInOrder(['Lời thì thầm', 'Bó hoa', '680.000 VNĐ'])
            ->assertSeeTextInOrder(['Bình yên xanh', 'Giỏ & hộp hoa', '750.000 VNĐ'])
            ->assertSee('/storage/products/1/loi-thi-tham.jpg', false)
            ->assertSee('data-filter-chip="bo-hoa"', false)
            ->assertSee('data-filter-chip="gio-hop-hoa"', false)
            ->assertDontSeeText('Hồng phấn ban mai')
            ->assertDontSeeText('Cúc họa mi tháng mười');
    }

    public function test_home_page_shows_at_most_eight_featured_products(): void
    {
        Product::factory()->featured()->count(ListFeaturedProductsAction::LIMIT + 2)->create();

        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(ListFeaturedProductsAction::LIMIT, substr_count($response->getContent(), 'data-filter-item'));
    }

    public function test_home_page_hides_filter_chips_for_a_single_category(): void
    {
        Product::factory()->featured()->count(2)->for(Category::factory())->create();

        $this->get(route('home'))->assertOk()->assertDontSee('data-filter-chip', false);
    }

    public function test_home_page_without_featured_products_shows_a_gentle_note(): void
    {
        app()->setLocale('vi');
        Product::factory()->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Lá đang chọn những bó hoa đẹp nhất, bạn ghé lại sau một chút nhé.')
            ->assertDontSee('data-filter-item', false);
    }

    public function test_home_page_shows_three_latest_published_posts(): void
    {
        app()->setLocale('vi');
        Post::factory()->create(['title' => 'Bài cũ nhất', 'created_at' => now()->subDays(5)]);
        Post::factory()->create(['title' => 'Mỗi màu hoa hồng, một lời muốn nói', 'created_at' => now()->subDays(3)]);
        Post::factory()->create(['title' => 'Một góc nhà, một bình hoa, một niềm vui', 'created_at' => now()->subDays(2)]);
        Post::factory()->create(['title' => 'Để hoa tươi lâu hơn, thương lâu hơn', 'created_at' => now()->subDay()]);
        Post::factory()->draft()->create(['title' => 'Bản nháp chưa đăng']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Ghé góc nhỏ của Lá')
            ->assertSee('href="'.route('posts.index').'"', false)
            ->assertSeeTextInOrder(['Để hoa tươi lâu hơn, thương lâu hơn', 'Một góc nhà, một bình hoa, một niềm vui', 'Mỗi màu hoa hồng, một lời muốn nói'])
            ->assertDontSeeText('Bài cũ nhất')
            ->assertDontSeeText('Bản nháp chưa đăng');
    }

    public function test_home_page_hides_journal_section_without_published_posts(): void
    {
        Post::factory()->draft()->create();

        $this->get(route('home'))->assertOk()->assertDontSee('id="journal-title"', false);
    }

    public function test_home_page_shows_active_occasions_linking_to_filtered_flowers(): void
    {
        app()->setLocale('vi');
        Category::factory()->create(['name' => 'Sinh nhật', 'slug' => 'sinh-nhat', 'description' => 'Thêm rực rỡ cho tuổi mới']);
        Category::factory()->create(['name' => 'Tình yêu', 'slug' => 'tinh-yeu']);
        Category::factory()->hidden()->create(['name' => 'Dịp đang ẩn']);
        Category::factory()->season()->create(['name' => 'Dịu dàng mùa thu']);
        Category::factory()->count(3)->create();

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSeeTextInOrder(['Mỗi dịp, một cách gửi thương.', 'Sinh nhật', 'Thêm rực rỡ cho tuổi mới', 'Tình yêu'])
            ->assertSee('href="'.e(route('flowers.index', ['occasion' => ['sinh-nhat']])).'"', false)
            ->assertDontSeeText('Dịp đang ẩn');

        $this->assertSame(ListHomeOccasionsAction::LIMIT, substr_count($response->getContent(), 'aria-label="Xem hoa cho dịp'));
    }

    public function test_home_page_hides_occasions_without_active_occasions(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('id="occasions-title"', false);
    }

    public function test_home_page_shows_season_banner_intro_and_consult_blocks(): void
    {
        app()->setLocale('vi');
        $this->travelTo(Carbon::create(2026, 10, 7));

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeTextInOrder(['Hoa theo mùa · Tháng 10', 'Thu ghé qua,', 'Bộ sưu tập Dịu dàng mùa thu · Từ 590.000 VNĐ', 'Ghé vườn hoa mùa thu'])
            ->assertSeeTextInOrder(['Một tiệm hoa nhỏ.', 'Thật nhiều thương yêu.', 'Đọc câu chuyện của Lá'])
            ->assertSee('href="'.route('story').'"', false)
            ->assertSeeTextInOrder(['Bạn kể chuyện. Lá chọn hoa.', 'Nhắn Lá tư vấn riêng', 'Kể Lá nghe', 'Cùng chọn điều xinh', 'Để Lá gửi thương'])
            ->assertSee('href="https://zalo.me/0378599249"', false)
            ->assertDontSeeText('Niềm vui nở từ những điều nhỏ');
    }

    public function test_home_page_renders_in_english(): void
    {
        app()->setLocale('en');
        Product::factory()->featured()->create(['name' => 'Lời thì thầm', 'base_price' => 680000]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Send a flower,')
            ->assertSeeText('Our most-loved bouquets')
            ->assertSeeText('680,000 VND');
    }

    public function test_seasons_page_shows_current_season_and_its_flowers(): void
    {
        app()->setLocale('vi');
        $this->travelTo(Carbon::create(2026, 8, 31));

        $this->get(route('seasons'))
            ->assertOk()
            ->assertSeeText('Hoa theo mùa · Tháng 8')
            ->assertSeeText('Thu ghé qua,')
            ->assertSeeText('Bộ sưu tập Dịu dàng mùa thu · Từ 590.000 VNĐ')
            ->assertSeeTextInOrder(['Tháng 8 – 10', 'Đang vào mùa', 'Thu dịu dàng'])
            ->assertSeeText('Những đóa hoa của tháng 8')
            ->assertSeeText('4 thiết kế dành cho mùa này')
            ->assertSeeText('Đúng mùa hoa,')
            ->assertSee('<span aria-current="page">Hoa theo mùa</span>', false);
    }

    public function test_seasons_page_follows_the_current_month(): void
    {
        app()->setLocale('vi');
        $this->travelTo(Carbon::create(2026, 11, 9));

        $this->get(route('seasons'))
            ->assertOk()
            ->assertSeeText('Hoa theo mùa · Tháng 11')
            ->assertSeeText('Đông về chậm rãi,')
            ->assertSeeText('Bộ sưu tập An lành mùa đông · Từ 650.000 VNĐ')
            ->assertSeeTextInOrder(['Tháng 11 – 1', 'Đang vào mùa', 'Đông an lành'])
            ->assertSeeText('Những đóa hoa của tháng 11')
            ->assertDontSeeText('Thu ghé qua,');
    }

    public function test_seasons_page_renders_in_english(): void
    {
        app()->setLocale('en');
        $this->travelTo(Carbon::create(2026, 11, 9));

        $this->get(route('seasons'))
            ->assertOk()
            ->assertSeeText('Seasonal flowers · November')
            ->assertSeeText('Winter slows down,')
            ->assertSeeText('In season now')
            ->assertSeeText('November’s flowers')
            ->assertSeeText('From 650,000 VND');
    }
}
