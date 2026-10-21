<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as GoogleUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class GoogleController extends Controller
{
    /**
     * Chuyển sang trang đăng nhập của Google.
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Google trả về: tìm hoặc tạo tài khoản rồi đăng nhập.
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            report($exception);
            return $this->failed('auth.google.failed');
        }

        if (! $googleUser->getEmail()) {
            return $this->failed('auth.google.no_email');
        }

        $user = $this->findOrCreateUser($googleUser);

        if (! $user) {
            return $this->failed('auth.google.unverified');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(url('/'));
    }

    /**
     * Ưu tiên tài khoản đã liên kết Google; nếu email đã có tài khoản thường thì liên kết
     * (chỉ khi Google xác nhận email), không thì tạo tài khoản mới không mật khẩu.
     */
    protected function findOrCreateUser(GoogleUser $googleUser): ?User
    {
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            return $user;
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (! $this->emailIsVerified($googleUser)) {
                return null;
            }

            $user->forceFill(['google_id' => $googleUser->getId()])->save();

            return $user;
        }

        return User::create([
            'name' => $googleUser->getName() ?: $googleUser->getEmail(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
        ]);
    }

    protected function emailIsVerified(GoogleUser $googleUser): bool
    {
        $userData = get_object_vars($googleUser)['user'] ?? [];

        return (bool) ($userData['email_verified'] ?? false);
    }

    protected function failed(string $messageKey): RedirectResponse
    {
        return redirect()->route('login')->with('auth_error', __($messageKey));
    }
}
