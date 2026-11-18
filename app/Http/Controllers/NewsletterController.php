<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Newsletter\SubscribeToNewsletterAction;
use App\Http\Requests\Storefront\SubscribeNewsletterRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Đăng ký nhận tin từ khối cuối Trang chủ (khách chưa đăng nhập).
 */
class NewsletterController extends Controller
{
    public function store(SubscribeNewsletterRequest $request, SubscribeToNewsletterAction $subscribe): RedirectResponse
    {
        $isNewSubscriber = $subscribe->execute($request->string('email')->toString(), app()->getLocale());
        $message = $isNewSubscriber ? 'newsletter.subscribed' : 'newsletter.already_subscribed';

        return back()
            ->withFragment('newsletter')
            ->with('newsletter_status', __($message, ['code' => config('shop.welcome_coupon')]));
    }
}
