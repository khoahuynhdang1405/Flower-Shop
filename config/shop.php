<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thông tin tiệm
    |--------------------------------------------------------------------------
    |
    | TODO: hotline đang mâu thuẫn giữa các màn hình (0378599249 / 0901 234 567 /
    | 0378590249 — docs/design-vs-db-gaps.md #4). Tạm dùng số trên footer trang
    | đăng nhập/đăng ký; khi chốt chỉ cần sửa ở đây.
    |
    */

    'hotline' => env('SHOP_HOTLINE', '0378599249'),

    'email' => env('SHOP_EMAIL', 'lavahoa2026@gmail.com'),

    /*
    | Ngưỡng miễn phí giao nội thành (VNĐ), hiển thị trên thanh xanh đầu trang.
    */

    'free_shipping_from' => 800000,

    /*
    |--------------------------------------------------------------------------
    | Menu storefront
    |--------------------------------------------------------------------------
    |
    | Nhãn lấy từ lang/{locale}/storefront.php (storefront.menu.{key}).
    | Mục có `route` chưa khai báo sẽ trỏ tạm về "#".
    |
    */

    'menu' => [
        ['key' => 'home', 'url' => '/'],
        ['key' => 'all_flowers', 'route' => 'flowers.index'],
        ['key' => 'occasions', 'route' => 'occasions.index'],
        ['key' => 'our_story', 'route' => 'story'],
        ['key' => 'journal', 'route' => 'posts.index'],
    ],

];
