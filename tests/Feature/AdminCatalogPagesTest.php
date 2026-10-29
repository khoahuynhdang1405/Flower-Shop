<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Admin › Bộ sưu tập và Admin › Ưu đãi hiện chỉ là giao diện với dữ liệu mẫu.
 */
class AdminCatalogPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_collections_page_renders_stats_cards_and_filters(): void
    {
        $this->get(route('admin.collections'))
            ->assertOk()
            ->assertSeeText('Những mùa hoa của tiệm')
            ->assertSeeText('5 đang hiển thị · 1 bản nháp')
            ->assertSeeText('Dịu dàng mùa thu')
            ->assertSeeText('12 sản phẩm · 01/10–30/11/2026')
            ->assertSeeText('5 sản phẩm · Dự kiến 01/12/2026')
            ->assertSee('data-list-select="type"', false)
            ->assertSee('<span aria-current="page">Bộ sưu tập</span>', false);
    }

    public function test_promotions_page_renders_stats_table_and_upcoming_program(): void
    {
        $this->get(route('admin.promotions'))
            ->assertOk()
            ->assertSeeText('Một chút thương, thêm một ưu đãi')
            ->assertSeeText('Trong các chương trình đang áp dụng')
            ->assertSeeText('CHAOHOA')
            ->assertDontSeeText('CHAOBAN10')
            ->assertSeeText('Tối đa 100.000 VNĐ')
            ->assertSeeText('Hiển thị tất cả 6 chương trình ưu đãi')
            ->assertSeeText('Mã THUONGME20 giảm 15% cho đơn từ 600.000 VNĐ, tối đa 100.000 VNĐ. Giới hạn 150 lượt, kết thúc ngày 20/10/2026.')
            ->assertSee('<span aria-current="page">Ưu đãi</span>', false);
    }

    public function test_promotions_page_renders_in_english(): void
    {
        app()->setLocale('en');

        $this->get(route('admin.promotions'))
            ->assertOk()
            ->assertSeeText('A little love, one more offer')
            ->assertSeeText('Up to 100,000 VND');
    }
}
