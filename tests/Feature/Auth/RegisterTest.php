<?php

namespace Tests\Feature\Auth;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nguyễn Minh Anh',
            'email' => 'minhanh@example.com',
            'phone' => '0901 234 567',
            'password' => 'Matkhau123',
            'password_confirmation' => 'Matkhau123',
            'terms' => '1',
        ], $overrides);
    }

    public function test_register_page_renders(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSeeText('Đăng ký')
            ->assertSeeText('Tạo một góc nhỏ cho những điều bạn thương.')
            ->assertSeeText('Tiếp tục với Google')
            ->assertSeeText('Tặng bạn 10% cho lần gửi thương đầu tiên');
    }

    public function test_user_can_register_and_is_logged_in(): void
    {
        $this->post(route('register.store'), $this->validData())
            ->assertRedirect('/')
            ->assertSessionHas('status');

        $user = User::where('email', 'minhanh@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('0901234567', $user->phone);
        $this->assertTrue(Hash::check('Matkhau123', $user->password));
        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_newsletter_opt_in_creates_subscriber(): void
    {
        $this->post(route('register.store'), $this->validData(['newsletter' => '1']));

        $this->assertTrue(Subscriber::where('email', 'minhanh@example.com')->exists());
    }

    public function test_existing_subscriber_is_not_duplicated(): void
    {
        Subscriber::create(['email' => 'minhanh@example.com']);

        $this->post(route('register.store'), $this->validData(['newsletter' => '1']))->assertRedirect('/');

        $this->assertDatabaseCount('subscribers', 1);
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->post(route('register.store'), $this->validData(['terms' => null]))
            ->assertSessionHasErrors(['terms' => __('auth.register.terms_required')]);

        $this->assertGuest();
    }

    public function test_password_needs_mixed_case_and_numbers(): void
    {
        $this->post(route('register.store'), $this->validData([
            'password' => 'matkhauyeu',
            'password_confirmation' => 'matkhauyeu',
        ]))->assertSessionHasErrors('password');
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->post(route('register.store'), $this->validData(['password_confirmation' => 'Khac12345']))
            ->assertSessionHasErrors('password');
    }

    public function test_phone_must_be_a_vietnamese_mobile_number(): void
    {
        $this->post(route('register.store'), $this->validData(['phone' => '12345']))
            ->assertSessionHasErrors(['phone' => __('auth.register.phone_invalid')]);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'minhanh@example.com']);

        $this->post(route('register.store'), $this->validData())
            ->assertSessionHasErrors('email');
    }

    public function test_validation_messages_are_in_vietnamese(): void
    {
        $this->post(route('register.store'), [])
            ->assertSessionHasErrors(['name' => 'Bạn điền họ và tên giúp Lá nhé.']);
    }
}
