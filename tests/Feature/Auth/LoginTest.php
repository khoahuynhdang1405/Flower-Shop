<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSeeText('Đăng nhập')
            ->assertSeeText('Ghé lại để tiếp tục gửi những điều thương.')
            ->assertSeeText('Tiếp tục với Google')
            ->assertSee(route('auth.google.redirect'), false)
            ->assertSee(route('register'), false);
    }

    public function test_user_can_log_in_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Matkhau123'])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_remember_me_sets_remember_token(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123', 'remember_token' => null]);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Matkhau123', 'remember' => '1']);

        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_wrong_password_shows_friendly_error(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123']);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'sai-mat-khau'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_google_only_account_cannot_log_in_with_password(): void
    {
        $user = User::factory()->google()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'bat-ky'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123']);

        for ($attempt = 0; $attempt < LoginRequest::MAX_ATTEMPTS; $attempt++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'sai']);
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Matkhau123'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect('/');
    }

    public function test_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
