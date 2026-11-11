<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    /*
    | Cổng VNPAY (thanh toán qua QR ngân hàng). Mặc định trỏ sandbox; tài khoản thử đăng ký tại
    | https://sandbox.vnpayment.vn/devreg. Lên production chỉ đổi VNPAY_URL và bộ khóa thật.
    */

    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'url' => env('VNPAY_URL') ?: 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
        'version' => '2.1.0',
        // Bỏ trống = khách tự chọn phương thức trên trang VNPAY (sandbox thử bằng thẻ NCB); "VNPAYQR" = chỉ mở QR.
        'bank_code' => env('VNPAY_BANK_CODE') ?: null,
        'expire_minutes' => 15,
    ],

    /*
    | Cổng MoMo (quét QR bằng ví MoMo). Mặc định trỏ sandbox; lên production đổi MOMO_ENDPOINT và bộ khóa thật.
    */

    'momo' => [
        'partner_code' => env('MOMO_PARTNER_CODE'),
        'access_key' => env('MOMO_ACCESS_KEY'),
        'secret_key' => env('MOMO_SECRET_KEY'),
        'endpoint' => env('MOMO_ENDPOINT') ?: 'https://test-payment.momo.vn/v2/gateway/api/create',
        'request_type' => 'captureWallet',
    ],

];
