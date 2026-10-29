<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.collections'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.promotions.store'))->assertRedirect(route('admin.login'));
    }

    public function test_customer_session_does_not_open_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_log_in_and_out(): void
    {
        $admin = Admin::factory()->create(['email' => 'admin@lavahoa.com', 'password' => 'Matkhau123']);

        $this->get(route('admin.login'))->assertOk()->assertSeeText('Đăng nhập quản trị');

        $this->post(route('admin.login.store'), ['email' => 'admin@lavahoa.com', 'password' => 'Matkhau123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');

        $this->get(route('admin.dashboard'))->assertOk()->assertSeeText($admin->name);

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_wrong_password_and_inactive_admin_are_rejected(): void
    {
        Admin::factory()->create(['email' => 'admin@lavahoa.com', 'password' => 'Matkhau123']);
        Admin::factory()->inactive()->create(['email' => 'nghi@lavahoa.com', 'password' => 'Matkhau123']);

        $this->post(route('admin.login.store'), ['email' => 'admin@lavahoa.com', 'password' => 'Sai123456'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->post(route('admin.login.store'), ['email' => 'nghi@lavahoa.com', 'password' => 'Matkhau123'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest('admin');
    }

    public function test_customer_credentials_cannot_log_in_to_admin(): void
    {
        User::factory()->create(['email' => 'khach@example.com', 'password' => 'Matkhau123']);

        $this->post(route('admin.login.store'), ['email' => 'khach@example.com', 'password' => 'Matkhau123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_customer_logout_keeps_admin_signed_in(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs(User::factory()->create())
            ->actingAs($admin, 'admin')
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_logout_keeps_customer_signed_in(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
        $this->assertAuthenticatedAs($customer, 'web');
    }

    public function test_storefront_footer_links_to_admin_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('href="'.route('admin.login').'"', false)
            ->assertSeeText('Dành cho nhân viên tiệm');
    }

    public function test_logged_in_admin_skips_login_page(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }
}
