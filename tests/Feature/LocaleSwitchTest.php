<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_switching_to_english_stores_choice_and_redirects_back(): void
    {
        $this->from(route('admin.dashboard'))
            ->post(route('locale.update', 'en'))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas(SetLocale::SESSION_KEY, 'en');
    }

    public function test_chosen_locale_is_applied_on_following_requests(): void
    {
        $this->withSession([SetLocale::SESSION_KEY => 'en'])
            ->get(route('admin.login'))
            ->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertSeeText('Admin sign in')
            ->assertSee('aria-pressed="true"', false);
    }

    public function test_default_locale_is_used_without_a_choice(): void
    {
        config(['app.locale' => 'vi']);
        app()->setLocale('vi');

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('<html lang="vi"', false)
            ->assertSeeText('Đăng nhập quản trị');
    }

    public function test_unsupported_locale_route_returns_not_found(): void
    {
        $this->post('/locale/fr')->assertNotFound();
    }

    public function test_unsupported_locale_in_session_is_ignored(): void
    {
        app()->setLocale('vi');

        $this->withSession([SetLocale::SESSION_KEY => 'fr'])
            ->get(route('admin.login'))
            ->assertOk()
            ->assertSee('<html lang="vi"', false);
    }
}
