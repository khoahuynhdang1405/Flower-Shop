<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Newsletter\SubscribeToNewsletterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Trang đăng ký.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Tạo tài khoản, ghi nhận đăng ký nhận tin (nếu chọn) rồi đăng nhập luôn.
     */
    public function store(RegisterRequest $request, SubscribeToNewsletterAction $subscribe): RedirectResponse
    {
        $user = DB::transaction(function () use ($request, $subscribe): User {
            $user = User::create($request->only('name', 'email', 'phone', 'password'));

            if ($request->boolean('newsletter')) {
                $subscribe->execute($user->email, app()->getLocale());
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(url('/'))
            ->with('status', __('auth.register.welcome', ['name' => $user->name]));
    }
}
