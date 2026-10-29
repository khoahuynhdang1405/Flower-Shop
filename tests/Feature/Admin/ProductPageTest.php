<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.products'))->assertRedirect(route('admin.login'));
    }

    public function test_products_page_shows_prices_by_size_and_stock(): void
    {
        app()->setLocale('vi');

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.products'))
            ->assertOk()
            ->assertSeeText('Sản phẩm & những lời thương')
            ->assertSeeText('LH-H001')
            ->assertSeeTextInOrder(['Nắng trong veo', 'Nhỏ', '450.000', 'Vừa', '550.000', 'Mặc định', 'Lớn', '750.000', '18 bó'])
            ->assertSeeText('3 bình')
            ->assertSeeText('Tồn kho dưới 5 sản phẩm')
            ->assertSeeText('Hiển thị tất cả 8 sản phẩm')
            ->assertSee('<span aria-current="page">Sản phẩm</span>', false);
    }

    public function test_products_page_renders_in_english(): void
    {
        app()->setLocale('en');

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.products'))
            ->assertOk()
            ->assertSeeTextInOrder(['Small', '450,000', 'Medium', '550,000'])
            ->assertSeeText('18 bouquets')
            ->assertSeeText('0 baskets');
    }
}
