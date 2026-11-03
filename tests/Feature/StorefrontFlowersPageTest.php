<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontFlowersPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_flowers_page_shows_filters_and_product_grid(): void
    {
        app()->setLocale('vi');

        $response = $this->get(route('flowers.index'));

        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('flowers.index'), '/').'"\s+aria-current="page"/', $response->getContent());

        $response
            ->assertOk()
            ->assertSeeText('Chọn hoa cho điều bạn thương.')
            ->assertSeeTextInOrder(['Lọc hoa', 'Kiểu hoa', 'Bó hoa (24)', 'Dịp gửi thương', 'Khoảng giá', 'Sắc hoa bạn thích'])
            ->assertSeeText('Hiển thị 1–9 trong 48 thiết kế')
            ->assertSeeTextInOrder(['Được yêu thích', 'Nắng trong veo', 'Hồng vàng · Cúc tana · Lá bạc', '550.000 VNĐ'])
            ->assertSee('<span aria-current="page">Tất cả hoa</span>', false)
            ->assertSee('name="price" value="" class="radio', false);

        $this->assertMatchesRegularExpression('/name="in_stock" value="1" class="checkbox [^"]*" checked>/', $response->getContent());
        $this->assertSame(9, substr_count($response->getContent(), '<article'));
    }

    public function test_flowers_page_keeps_the_chosen_filters_and_page(): void
    {
        $response = $this->get(route('flowers.index', ['type' => ['basket'], 'price' => '500_800', 'color' => ['red'], 'sort' => 'newest', 'page' => 2]))
            ->assertOk();

        $html = $response->getContent();

        $this->assertMatchesRegularExpression('/name="type\[\]" value="basket" class="[^"]*" checked>/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="type\[\]" value="bouquet" class="[^"]*" checked>/', $html);
        $this->assertMatchesRegularExpression('/name="price" value="500_800" class="[^"]*" checked>/', $html);
        $this->assertMatchesRegularExpression('/name="color\[\]" value="red" class="[^"]*" checked>/', $html);
        $this->assertMatchesRegularExpression('/value="newest"\s+selected/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="in_stock" value="1" class="[^"]*" checked>/', $html);
        $this->assertMatchesRegularExpression('/aria-label="[^"]*2"\s+aria-current="page"/', $html);
    }

    public function test_flowers_page_renders_in_english(): void
    {
        app()->setLocale('en');

        $this->get(route('flowers.index'))
            ->assertOk()
            ->assertSeeText('Choose flowers for what you love.')
            ->assertSeeText('Showing 1–9 of 48 designs')
            ->assertSeeText('550,000 VND');
    }
}
