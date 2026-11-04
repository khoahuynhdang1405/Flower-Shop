<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\FlowerColor;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontFlowersPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_flowers_page_lists_products_on_sale_with_filter_options_from_the_database(): void
    {
        app()->setLocale('vi');
        $bouquets = Category::factory()->flowerType()->create(['name' => 'Bó hoa', 'slug' => 'bo-hoa']);
        Category::factory()->flowerType()->create(['name' => 'Hoa cắm bình', 'slug' => 'hoa-cam-binh']);
        Category::factory()->flowerType()->hidden()->create(['name' => 'Kiểu đang ẩn']);
        Category::factory()->create(['name' => 'Sinh nhật', 'slug' => 'sinh-nhat']);
        $whisper = Product::factory()->for($bouquets)->create(['name' => 'Lời thì thầm', 'base_price' => 480000]);
        ProductSize::factory()->for($whisper)->create(['price' => 680000, 'is_default' => true]);
        Product::factory()->for($bouquets)->hidden()->create(['name' => 'Đã ngừng bán']);

        $response = $this->get(route('flowers.index'));

        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('flowers.index'), '/').'"\s+aria-current="page"/', $response->getContent());

        $response
            ->assertOk()
            ->assertSeeText('Chọn hoa cho điều bạn thương.')
            ->assertSeeTextInOrder(['Kiểu hoa', 'Bó hoa (1)', 'Hoa cắm bình (0)', 'Dịp gửi thương', 'Sinh nhật', 'Khoảng giá'])
            ->assertSee('name="type[]" value="bo-hoa"', false)
            ->assertSee('name="occasion[]" value="sinh-nhat"', false)
            ->assertSeeText('Hiển thị 1–1 trong 1 thiết kế')
            ->assertSeeTextInOrder(['Lời thì thầm', 'Bó hoa', '680.000 VNĐ'])
            ->assertDontSeeText('Kiểu đang ẩn')
            ->assertDontSeeText('Đã ngừng bán')
            ->assertDontSee('aria-label="Phân trang"', false);
    }

    public function test_flowers_page_applies_the_chosen_filters_and_keeps_them_checked(): void
    {
        $baskets = Category::factory()->flowerType()->create(['slug' => 'gio-hop-hoa']);
        $birthday = Category::factory()->create(['slug' => 'sinh-nhat']);
        Product::factory()->for($baskets)->hasAttached($birthday, [], 'occasions')->create(['name' => 'Mây trắng', 'base_price' => 650000]);
        Product::factory()->for($baskets)->create(['name' => 'Không có dịp', 'base_price' => 650000]);
        Product::factory()->create(['name' => 'Bó hoa khác', 'base_price' => 650000]);

        $response = $this->get(route('flowers.index', ['type' => ['gio-hop-hoa'], 'occasion' => ['sinh-nhat'], 'price' => '500_800']))
            ->assertOk()
            ->assertSeeText('Mây trắng')
            ->assertDontSeeText('Không có dịp')
            ->assertDontSeeText('Bó hoa khác');

        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/name="type\[\]" value="gio-hop-hoa" class="[^"]*" checked>/', $html);
        $this->assertMatchesRegularExpression('/name="occasion\[\]" value="sinh-nhat" class="[^"]*" checked>/', $html);
        $this->assertMatchesRegularExpression('/name="price" value="500_800" class="[^"]*" checked>/', $html);
    }

    public function test_flowers_page_filters_by_color_and_keeps_the_swatch_checked(): void
    {
        Product::factory()->hasColors(1, ['color' => FlowerColor::Pink])->create(['name' => 'Lời thì thầm']);
        Product::factory()->hasColors(1, ['color' => FlowerColor::Yellow])->create(['name' => 'Nắng trong veo']);

        $html = $this->get(route('flowers.index', ['color' => ['pink']]))
            ->assertOk()
            ->assertSeeText('Lời thì thầm')
            ->assertDontSeeText('Nắng trong veo')
            ->getContent();

        $this->assertMatchesRegularExpression('/name="color\[\]" value="pink" class="sr-only" checked>/', $html);
        $this->get(route('flowers.index', ['color' => ['purple']]))->assertSessionHasErrors('color.0');
    }

    public function test_flowers_page_shows_a_gentle_note_when_nothing_matches(): void
    {
        app()->setLocale('vi');
        Product::factory()->create(['base_price' => 450000]);

        $this->get(route('flowers.index', ['price' => 'over_1200']))
            ->assertOk()
            ->assertSeeText('Chưa có đóa hoa nào hợp với lựa chọn này.')
            ->assertDontSee('<article', false);
    }

    public function test_flowers_page_paginates_nine_per_page_and_keeps_the_filters_in_page_links(): void
    {
        app()->setLocale('vi');
        $bouquets = Category::factory()->flowerType()->create(['slug' => 'bo-hoa']);
        Product::factory()->for($bouquets)->count(10)->create();

        $response = $this->get(route('flowers.index', ['type' => ['bo-hoa']]))->assertOk();
        $html = $response->getContent();

        $this->assertSame(9, substr_count($html, '<article'));
        $this->assertStringContainsString('type%5B0%5D=bo-hoa&amp;page=2', $html);

        $this->get(route('flowers.index', ['type' => ['bo-hoa'], 'page' => 2]))
            ->assertOk()
            ->assertSeeText('Hiển thị 10–10 trong 10 thiết kế');
    }

    public function test_an_unknown_price_range_is_rejected(): void
    {
        $this->get(route('flowers.index', ['price' => 'free']))->assertSessionHasErrors('price');
    }

    public function test_flowers_page_renders_in_english(): void
    {
        app()->setLocale('en');
        Product::factory()->create(['base_price' => 550000]);

        $this->get(route('flowers.index'))
            ->assertOk()
            ->assertSeeText('Choose flowers for what you love.')
            ->assertSeeText('Showing 1–1 of 1 designs')
            ->assertSeeText('550,000 VND');
    }
}
