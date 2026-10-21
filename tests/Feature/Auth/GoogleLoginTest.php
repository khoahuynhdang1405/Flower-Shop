<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    private function googleUser(string $email = 'thuha@gmail.com', bool $verified = true): SocialiteUser
    {
        return (new SocialiteUser)
            ->setRaw(['sub' => '1098765', 'email' => $email, 'email_verified' => $verified])
            ->map(['id' => '1098765', 'name' => 'Phạm Thu Hà', 'email' => $email]);
    }

    public function test_redirects_to_google(): void
    {
        Socialite::fake('google');

        $this->get(route('auth.google.redirect'))->assertRedirect();
    }

    public function test_new_google_user_gets_an_account_without_password(): void
    {
        Socialite::fake('google', $this->googleUser());

        $this->get(route('auth.google.callback'))->assertRedirect('/');

        $user = User::where('email', 'thuha@gmail.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('1098765', $user->google_id);
        $this->assertNull($user->password);
    }

    public function test_returning_google_user_logs_into_same_account(): void
    {
        $user = User::factory()->google()->create(['google_id' => '1098765', 'email' => 'cu@gmail.com']);
        Socialite::fake('google', $this->googleUser('moi@gmail.com'));

        $this->get(route('auth.google.callback'))->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_verified_google_email_is_linked_to_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'thuha@gmail.com']);
        Socialite::fake('google', $this->googleUser());

        $this->get(route('auth.google.callback'))->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('1098765', $user->fresh()->google_id);
        $this->assertNotNull($user->fresh()->password);
    }

    public function test_unverified_google_email_is_not_linked_to_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'thuha@gmail.com']);
        Socialite::fake('google', $this->googleUser(verified: false));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', __('auth.google.unverified'));

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_cancelled_or_failed_google_login_returns_to_login_page(): void
    {
        Socialite::shouldReceive('driver->user')->andThrow(new Exception('access_denied'));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('auth_error', __('auth.google.failed'));

        $this->assertGuest();
    }
}
