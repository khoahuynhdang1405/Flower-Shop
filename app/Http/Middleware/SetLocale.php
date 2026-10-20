<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Session key lưu ngôn ngữ người dùng đã chọn.
     */
    public const SESSION_KEY = 'locale';

    /**
     * Áp dụng ngôn ngữ đã chọn trong session; bỏ qua giá trị không được hỗ trợ.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get(self::SESSION_KEY);

        if (is_string($locale) && array_key_exists($locale, config('app.supported_locales'))) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
