<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdateAddressRequest;
use App\Http\Requests\Account\UpdatePasswordRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Trang Tài khoản: thông tin cá nhân, địa chỉ giao mặc định, đơn hàng, đổi mật khẩu.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('account.show', [
            'user' => $user,
            'customer' => $this->customerSummary($user),
        ]);
    }

    /**
     * Cập nhật họ tên và số điện thoại.
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->to(route('account').'#profile')->with('status', 'profile-updated');
    }

    /**
     * Cập nhật địa chỉ giao hoa mặc định.
     */
    public function updateAddress(UpdateAddressRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->to(route('account').'#address')->with('status', 'address-updated');
    }

    /**
     * Đổi mật khẩu. Mật khẩu được hash qua cast `hashed` của User.
     *
     * Các thiết bị khác đang đăng nhập (kể cả "Ghi nhớ đăng nhập") sẽ bị đăng xuất ở lần truy cập kế tiếp
     * nhờ middleware AuthenticateSession (bootstrap/app.php) so khớp hash mật khẩu trong session.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        $request->session()->regenerate();

        return redirect()->to(route('account').'#password')->with('status', 'password-updated');
    }

    /**
     * Dữ liệu cho thẻ bên trái và lời chào.
     *
     * @return array{name: string, short_name: string, initials: string, group: string, member_since: string, saved_at: string}
     */
    private function customerSummary(User $user): array
    {
        // "Huỳnh Đăng Khoa" → gọi "Đăng Khoa", avatar "ĐK".
        $words = preg_split('/\s+/u', trim($user->name)) ?: [$user->name];
        $calledWords = array_slice($words, count($words) >= 3 ? -2 : -1);

        return [
            'name' => $user->name,
            'short_name' => implode(' ', $calledWords),
            'initials' => Str::upper(implode('', array_map(fn (string $word): string => Str::substr($word, 0, 1), array_slice($words, -2)))),
            'group' => $user->customer_group->label(),
            'member_since' => $user->created_at->format('m/Y'),
            'saved_at' => $user->updated_at->format('H:i, d/m/Y'),
        ];
    }
}
