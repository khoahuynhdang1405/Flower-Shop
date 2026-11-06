<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontProductPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_product_page_shows_the_product_with_its_sizes_and_images_from_the_database(): void
    {
        app()->setLocale('vi');
        $whisper = Product::factory()
            ->for(Category::factory()->flowerType()->create(['name' => 'Bó hoa tươi']))
            ->create([
                'name' => 'Lời thì thầm',
                'slug' => 'loi-thi-tham',
                'description' => 'Một chút hồng đào, một chút kem ấm.',
                'dimension_info' => 'cao khoảng 40 cm · đường kính 30 cm',
                'base_price' => 480000,
            ]);
        $large = ProductSize::factory()->for($whisper)->create(['size_name' => 'Lớn', 'price' => 880000]);
        $medium = ProductSize::factory()->for($whisper)->create(['size_name' => 'Vừa', 'price' => 680000, 'is_default' => true]);
        $small = ProductSize::factory()->for($whisper)->create(['size_name' => 'Nhỏ', 'price' => 480000]);
        ProductImage::factory()->for($whisper)->create(['image_path' => 'products/thu-hai.jpg', 'sort_order' => 2]);
        ProductImage::factory()->for($whisper)->primary()->create(['image_path' => 'products/dau-tien.jpg', 'sort_order' => 1]);
        ProductImage::factory()->for($whisper)->create(['image_path' => 'products/rieng-co-lon.jpg', 'product_size_id' => $large->id, 'sort_order' => 3]);

        $response = $this->get(route('flowers.show', 'loi-thi-tham'));
        $html = $response->getContent();

        $response
            ->assertOk()
            ->assertSee('href="'.route('flowers.index').'"', false)
            ->assertSee('<span aria-current="page">Lời thì thầm</span>', false)
            ->assertSeeTextInOrder(['Bó hoa tươi', 'Lời thì thầm', '680.000 VNĐ', 'Một chút hồng đào, một chút kem ấm.'])
            ->assertSeeTextInOrder(['Chọn kích thước bó hoa', 'Nhỏ', '480.000 VNĐ', 'Vừa', '680.000 VNĐ', 'Lớn', '880.000 VNĐ'])
            ->assertSeeText('Cỡ vừa: cao khoảng 40 cm · đường kính 30 cm')
            ->assertSeeInOrder([
                asset('storage/products/dau-tien.jpg'),
                asset('storage/products/thu-hai.jpg'),
                asset('storage/products/rieng-co-lon.jpg'),
            ], false)
            ->assertSeeTextInOrder(['Thêm vào giỏ', 'Mua ngay', 'Tươi xinh từ 3–5 ngày']);

        $this->assertSame(3, substr_count($html, 'data-gallery-thumb'));
        $this->assertMatchesRegularExpression('/value="'.$medium->id.'"\s+checked\s+data-size-option data-price="680.000 VNĐ"/', $html);
        $this->assertMatchesRegularExpression('/value="'.$small->id.'"\s+data-size-option data-price="480.000 VNĐ" data-dimension="Cỡ nhỏ: cao khoảng 40 cm/', $html);
    }

    public function test_product_without_sizes_or_images_shows_base_price_and_a_placeholder(): void
    {
        app()->setLocale('vi');
        Product::factory()->create(['slug' => 'may-trang', 'base_price' => 550000, 'dimension_info' => null]);

        $response = $this->get(route('flowers.show', 'may-trang'));

        $response
            ->assertOk()
            ->assertSeeText('550.000 VNĐ')
            ->assertDontSeeText('Chọn kích thước bó hoa')
            ->assertDontSee('data-size-dimension', false);

        $this->assertSame(1, substr_count($response->getContent(), 'data-gallery-thumb'));
    }

    public function test_unknown_or_hidden_products_are_not_found(): void
    {
        Product::factory()->hidden()->create(['slug' => 'da-ngung-ban']);

        $this->get(route('flowers.show', 'khong-ton-tai'))->assertNotFound();
        $this->get(route('flowers.show', 'da-ngung-ban'))->assertNotFound();
    }

    public function test_product_page_offers_active_gift_addons_with_the_free_card_preselected(): void
    {
        app()->setLocale('vi');
        $product = Product::factory()->create(['slug' => 'loi-thi-tham']);
        $card = Addon::factory()->free()->create(['name' => 'Thiệp viết tay']);
        Addon::factory()->create(['name' => 'Hộp quà nhỏ', 'price' => 80000]);
        Addon::factory()->create(['name' => 'Gấu bông êm ái', 'price' => 150000]);
        Addon::factory()->create(['name' => 'Chocolate ngọt ngào', 'price' => 120000]);
        Addon::factory()->hidden()->create(['name' => 'Nến thơm']);

        $response = $this->get(route('flowers.show', 'loi-thi-tham'));
        $html = $response->getContent();

        $response
            ->assertOk()
            ->assertSee('action="'.route('cart.flowers.store').'"', false)
            ->assertSee('name="product_id" value="'.$product->id.'"', false)
            ->assertSeeText('Thêm một chút thương yêu')
            ->assertSeeTextInOrder(['Hộp quà nhỏ', '80.000 VNĐ', 'Gấu bông êm ái', '150.000 VNĐ', 'Chocolate ngọt ngào', '120.000 VNĐ', 'Thiệp viết tay', 'Miễn phí'])
            ->assertDontSeeText('Nến thơm')
            ->assertSee('<p class="text-[13px] text-lh-addon-muted" aria-live="polite" data-addon-summary>Đã chọn thiệp viết tay</p>', false);

        $this->assertSame(4, substr_count($html, 'name="addons[]"'));
        $this->assertSame(1, preg_match_all('/name="addons\[\]" value="\d+"\s+checked/', $html));
        $this->assertMatchesRegularExpression('/name="addons\[\]" value="'.$card->id.'"\s+checked\s+data-addon-option data-name="thiệp viết tay" data-price="0"/', $html);
    }

    public function test_product_page_renders_in_english(): void
    {
        app()->setLocale('en');
        $product = Product::factory()->create(['slug' => 'loi-thi-tham']);
        ProductSize::factory()->for($product)->create(['price' => 680000, 'is_default' => true]);

        $this->get(route('flowers.show', 'loi-thi-tham'))
            ->assertOk()
            ->assertSeeText('Choose a bouquet size')
            ->assertSeeText('680,000 VND')
            ->assertSeeText('Add to cart');
    }
}
