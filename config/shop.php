<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thông tin tiệm
    |--------------------------------------------------------------------------
    */

    'hotline' => env('SHOP_HOTLINE', '0378599249'),

    'email' => env('SHOP_EMAIL', 'lavahoa@gmail.com'),

    'address' => '24 Nguyễn Văn Thủ, phường Tân Định, TP.HCM',

    'opening_hours' => '08:00 – 20:00',

    // Link mạng xã hội thật.
    'socials' => [
        'instagram' => '#',
        'facebook' => '#',
    ],

    /*
    | Ngưỡng miễn phí giao nội thành (VNĐ), hiển thị trên thanh xanh đầu trang.
    */

    'free_shipping_from' => 800000,

    /*
    | Quận/Huyện TP.HCM cho ô chọn địa chỉ giao hoa mặc định (users.default_shipping_district).
    | Giá trị lưu vào DB chính là tên hiển thị.
    */

    'districts' => [
        'Quận 1',
        'Quận 3',
        'Quận 4',
        'Quận 5',
        'Quận 6',
        'Quận 7',
        'Quận 8',
        'Quận 10',
        'Quận 11',
        'Quận 12',
        'Quận Bình Tân',
        'Quận Bình Thạnh',
        'Quận Gò Vấp',
        'Quận Phú Nhuận',
        'Quận Tân Bình',
        'Quận Tân Phú',
        'Huyện Bình Chánh',
        'Huyện Cần Giờ',
        'Huyện Củ Chi',
        'Huyện Hóc Môn',
        'Huyện Nhà Bè',
    ],

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
