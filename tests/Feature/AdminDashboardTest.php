<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->actingAs(Admin::factory()->create(['name' => 'Đăng Khoa']), 'admin');
    }

    public function test_dashboard_renders_layout_in_vietnamese(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 09:00:00'));
        app()->setLocale('vi');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Một ngày đẹp ở Lá & Hoa')
            ->assertSeeText('Thứ Tư, 07 tháng 10, 2026 · Cùng chăm chút những điều thương.')
            ->assertSeeText('Hôm nay, 07/10')
            ->assertSeeText('Không gian quản trị')
            ->assertSeeText('© 2026 Lá & Hoa · Không gian quản trị nội bộ')
            ->assertSee('<span aria-current="page">Tổng quan</span>', false);
    }

    public function test_dashboard_renders_in_english(): void
    {
        $this->travelTo(Carbon::parse('2026-10-07 09:00:00'));
        app()->setLocale('en');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('A lovely day at Lá & Hoa')
            ->assertSeeText('Wednesday, 7 October 2026')
            ->assertSeeText('Today, Oct 7')
            ->assertSee('<html lang="en"', false);
    }

    public function test_dashboard_menu_item_is_the_only_active_link(): void
    {
        $html = $this->get(route('admin.dashboard'))->getContent();

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(route('admin.dashboard'), '/').'"\s+aria-current="page"/',
            $html,
        );
        // Một aria-current cho mục menu, một cho breadcrumb.
        $this->assertSame(2, substr_count($html, 'aria-current="page"'));
    }

    public function test_menu_items_without_routes_fall_back_to_placeholder_links(): void
    {
        app()->setLocale('vi');

        $this->get(route('admin.dashboard'))
            ->assertSeeText('Đơn hàng')
            ->assertSee('href="#"', false);
    }
}
