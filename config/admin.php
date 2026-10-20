<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu trang quản trị
    |--------------------------------------------------------------------------
    |
    | Nguồn duy nhất cho sidebar và breadcrumb. Nhãn lấy từ lang/{locale}/admin.php
    | theo `key` (admin.menu.{key}). Mục có `route` chưa được khai báo sẽ trỏ tạm
    | về "#" cho tới khi trang đó được làm.
    | `icon` là tên Blade component: icon Lucide (lucide-*, mallardduck/blade-lucide-icons)
    | hoặc biểu tượng riêng của tiệm (brand.*).
    |
    */

    'menu' => [
        ['key' => 'dashboard', 'route' => 'admin.dashboard', 'icon' => 'lucide-layout-grid'],
        ['key' => 'orders', 'route' => 'admin.orders', 'icon' => 'lucide-shopping-bag', 'badge' => 'orders'],
        ['key' => 'products', 'route' => 'admin.products', 'icon' => 'brand.flower-mark'],
        ['key' => 'collections', 'route' => 'admin.collections', 'icon' => 'lucide-layers'],
        ['key' => 'customers', 'route' => 'admin.customers', 'icon' => 'lucide-users'],
        ['key' => 'posts', 'route' => 'admin.posts', 'icon' => 'lucide-book-open'],
        ['key' => 'promotions', 'route' => 'admin.promotions', 'icon' => 'lucide-ticket-percent'],
    ],

    'secondary_menu' => [
        ['key' => 'settings', 'route' => 'admin.settings'],
        ['key' => 'help', 'route' => 'admin.help'],
        ['key' => 'storefront', 'url' => '/'],
    ],

];
