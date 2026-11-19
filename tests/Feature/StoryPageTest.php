<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Trang tĩnh "Câu chuyện của Lá" (docs/designs/storefront/09-cau-chuyen-cua-la.png).
 */
class StoryPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_story_page_tells_the_shop_story_in_order(): void
    {
        app()->setLocale('vi');

        $this->get(route('story'))
            ->assertOk()
            ->assertSeeTextInOrder([
                'Câu chuyện của Lá',
                'Một tiệm hoa nhỏ.',
                '“Từ đôi tay, đến trái tim.”',
                'Ba điều mà Lá & Hoa luôn nâng niu',
                'Hoa chọn lọc', 'Gói tay độc bản', 'Thiệp viết tay',
                'Một ngày bình dị tại Lá & Hoa',
                '07:00', 'Đón hoa, đón một ngày mới',
                '18:00', 'Gửi thương đi, giữ niềm vui lại',
                'Bạn kể chuyện. Lá chọn hoa.',
                'Nhắn Lá để tư vấn riêng',
            ])
            ->assertSee('href="#philosophy-title"', false);
    }

    public function test_consult_button_opens_the_shop_zalo_in_a_new_tab(): void
    {
        $this->get(route('story'))
            ->assertOk()
            ->assertSee('href="https://zalo.me/0378599249" target="_blank" rel="noopener"', false);
    }

    public function test_menu_marks_the_story_page_as_current(): void
    {
        $html = $this->get(route('story'))->getContent();

        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('story'), '/').'"\s+aria-current="page"/', $html);
    }

    public function test_story_page_renders_in_english(): void
    {
        app()->setLocale('en');

        $this->get(route('story'))
            ->assertOk()
            ->assertSeeText('A small flower shop.')
            ->assertSeeText('Three things Lá & Hoa always cherish')
            ->assertSeeText('Message Lá for advice');
    }
}
