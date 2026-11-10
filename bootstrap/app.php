<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // API giỏ hàng dùng session (khách chưa đăng nhập) nên chạy với middleware "web", không phải "api".
        then: function (): void {
            Route::middleware('web')->prefix('api')->name('api.')->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Đổi mật khẩu thì các phiên đăng nhập khác hết hiệu lực.
        $middleware->authenticateSessions();

        // Khách và quản trị có trang đăng nhập / trang chủ riêng.
        $middleware->redirectGuestsTo(fn (Request $request): string => $request->is('admin', 'admin/*') ? route('admin.login') : route('login'));
        $middleware->redirectUsersTo(fn (Request $request): string => $request->is('admin', 'admin/*') ? route('admin.dashboard') : url('/'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
