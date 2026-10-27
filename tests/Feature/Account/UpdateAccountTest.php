<?php

namespace Tests\Feature\Account;

use App\Enums\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_guest_cannot_update_account(): void
    {
        $this->patch(route('account.profile.update'), ['name' => 'Ai đó', 'phone' => '0901234567'])
            ->assertRedirect(route('login'));
        $this->patch(route('account.address.update'))->assertRedirect(route('login'));
        $this->put(route('account.password.update'))->assertRedirect(route('login'));
    }

    public function test_user_can_update_profile_and_phone_is_normalized(): void
    {
        $user = User::factory()->create(['email' => 'khoa@example.com']);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), [
                'name' => '  Huỳnh Đăng Khoa ',
                'phone' => '0378 599.249',
                'birthday' => '2005-04-01',
                'email' => 'doi-email@example.com',
                'customer_group' => 'best_friend',
            ])
            ->assertRedirect(route('account').'#profile')
            ->assertSessionHas('status', 'profile-updated');

        $user->refresh();
        $this->assertSame('Huỳnh Đăng Khoa', $user->name);
        $this->assertSame('0378599249', $user->phone);
        $this->assertSame('2005-04-01', $user->birthday->format('Y-m-d'));
        $this->assertSame('khoa@example.com', $user->email);
        $this->assertSame(CustomerGroup::NewFriend, $user->customer_group);
    }

    public function test_birthday_is_optional_but_must_be_in_the_past(): void
    {
        $user = User::factory()->create(['birthday' => '2005-04-01']);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), ['name' => 'Khoa', 'phone' => '0901234567', 'birthday' => now()->addDay()->format('Y-m-d')])
            ->assertSessionHasErrors(['birthday' => __('account.profile.birthday_invalid')]);

        $this->actingAs($user)
            ->patch(route('account.profile.update'), ['name' => 'Khoa', 'phone' => '0901234567', 'birthday' => ''])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->birthday);
    }

    public function test_profile_rejects_invalid_phone(): void
    {
        $user = User::factory()->create(['phone' => '0901234567']);

        $this->actingAs($user)
            ->from(route('account'))
            ->patch(route('account.profile.update'), ['name' => 'Khoa', 'phone' => '0123456789'])
            ->assertRedirect(route('account').'#profile')
            ->assertSessionHasErrors(['phone' => __('auth.register.phone_invalid')]);

        $this->assertSame('0901234567', $user->refresh()->phone);
    }

    public function test_user_can_save_default_shipping_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.address.update'), [
                'default_shipping_address' => '18 Võ Văn Tần, Phường Xuân Hòa',
                'default_shipping_district' => 'Quận 3',
            ])
            ->assertRedirect(route('account').'#address')
            ->assertSessionHas('status', 'address-updated');

        $user->refresh();
        $this->assertSame('18 Võ Văn Tần, Phường Xuân Hòa', $user->default_shipping_address);
        $this->assertSame('Quận 3', $user->default_shipping_district);
    }

    public function test_district_must_be_from_the_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.address.update'), [
                'default_shipping_address' => '18 Võ Văn Tần, Phường 6',
                'default_shipping_district' => 'Quận 2',
            ])
            ->assertSessionHasErrors(['default_shipping_district' => __('account.address.district_required')]);

        $this->assertNull($user->refresh()->default_shipping_district);
    }

    public function test_default_shipping_address_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('account.address.update'), ['default_shipping_address' => ' ', 'default_shipping_district' => ''])
            ->assertSessionHasErrors([
                'default_shipping_address' => __('account.address.street_required'),
                'default_shipping_district' => __('account.address.district_required'),
            ]);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123']);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'Matkhau123',
                'password' => 'Hoamoi2026',
                'password_confirmation' => 'Hoamoi2026',
            ])
            ->assertRedirect(route('account').'#password')
            ->assertSessionHas('status', 'password-updated');

        $this->assertTrue(Hash::check('Hoamoi2026', $user->refresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123']);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'Saimatkhau1',
                'password' => 'Hoamoi2026',
                'password_confirmation' => 'Hoamoi2026',
            ])
            ->assertRedirect(route('account').'#password')
            ->assertSessionHasErrors(['current_password' => __('account.password.current_wrong')]);

        $this->assertTrue(Hash::check('Matkhau123', $user->refresh()->password));
    }

    public function test_new_password_must_be_strong_confirmed_and_different(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123']);

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'Matkhau123',
                'password' => 'yeu',
                'password_confirmation' => 'khac',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'current_password' => 'Matkhau123',
                'password' => 'Matkhau123',
                'password_confirmation' => 'Matkhau123',
            ])
            ->assertSessionHasErrors(['password' => __('account.password.same_as_current')]);
    }

    public function test_google_only_user_can_set_password_without_current_one(): void
    {
        $user = User::factory()->google()->create();

        $this->actingAs($user)
            ->put(route('account.password.update'), [
                'password' => 'Hoamoi2026',
                'password_confirmation' => 'Hoamoi2026',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Hoamoi2026', $user->refresh()->password));
    }

    public function test_changing_password_signs_out_other_sessions(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123']);

        // Một thiết bị khác đã đăng nhập: session lưu hash mật khẩu cũ.
        $this->actingAs($user)->get(route('account'))->assertOk();
        $oldSession = session()->all();

        $user->update(['password' => 'Hoamoi2026']);

        $this->flushSession();
        $this->withSession($oldSession)
            ->actingAs($user->refresh())
            ->get(route('account'))
            ->assertRedirect(route('login'));
    }

    public function test_password_update_is_rate_limited(): void
    {
        $user = User::factory()->create(['password' => 'Matkhau123']);
        $payload = ['current_password' => 'Saimatkhau1', 'password' => 'Hoamoi2026', 'password_confirmation' => 'Hoamoi2026'];

        foreach (range(1, 6) as $attempt) {
            $this->actingAs($user)->put(route('account.password.update'), $payload);
        }

        $this->actingAs($user)->put(route('account.password.update'), $payload)->assertTooManyRequests();
    }
}
