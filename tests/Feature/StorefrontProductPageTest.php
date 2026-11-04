<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontProductPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_product_page_shows_gallery_sizes_and_purchase_options(): void
    {
        app()->setLocale('vi');

        $response = $this->get(route('flowers.show', 'loi-thi-tham'));
        $html = $response->getContent();

        $response
            ->assertOk()
            ->assertSee('href="'.route('flowers.index').'"', false)
            ->assertSee('<span aria-current="page">Lời thì thầm</span>', false)
            ->assertSeeTextInOrder(['Lời thì thầm', '680.000 VNĐ', 'Chọn kích thước bó hoa', 'Nhỏ', '480.000 VNĐ', 'Vừa', '680.000 VNĐ', 'Lớn', '880.000 VNĐ'])
            ->assertSeeText('Cỡ vừa: cao khoảng 40 cm · đường kính 30 cm')
            ->assertSeeText('Video · 00:18')
            ->assertSeeTextInOrder(['Thêm vào giỏ', 'Mua ngay', 'Tươi xinh từ 3–5 ngày'])
            ->assertSeeText('Phí từ 25.000 VNĐ theo khu vực');

        $this->assertSame(4, substr_count($html, 'data-gallery-thumb'));
        $this->assertSame(1, substr_count($html, 'data-gallery-thumb aria-pressed="true"'));
        $this->assertMatchesRegularExpression('/value="medium"\s+checked\s+data-size-option data-price="680.000 VNĐ"/', $html);
        $this->assertMatchesRegularExpression('/value="small"\s+data-size-option data-price="480.000 VNĐ" data-dimension="Cỡ nhỏ: [^"]+"/', $html);
    }

    public function test_product_page_renders_in_english(): void
    {
        app()->setLocale('en');

        $this->get(route('flowers.show', 'loi-thi-tham'))
            ->assertOk()
            ->assertSeeText('Choose a bouquet size')
            ->assertSeeText('680,000 VND')
            ->assertSeeText('Add to cart');
    }
}
