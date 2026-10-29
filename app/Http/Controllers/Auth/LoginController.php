<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Trang đăng nhập.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Đăng nhập bằng email + mật khẩu.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(url('/'));
    }

    /**
     * Đăng xuất. Chỉ đổi mã phiên thay vì xóa cả phiên, để phiên quản trị (guard admin) trên cùng trình duyệt vẫn giữ.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->forget('password_hash_web');
        $request->session()->regenerate(true);
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
