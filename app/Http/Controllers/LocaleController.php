<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Lưu ngôn ngữ người dùng chọn rồi quay lại trang trước.
     * Mã ngôn ngữ đã được giới hạn ở route (whereIn).
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $request->session()->put(SetLocale::SESSION_KEY, $locale);

        return back();
    }
}
