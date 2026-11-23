<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Tài khoản khách mẫu để chạy thử storefront (đặt hoa, tra cứu đơn, trang Tài khoản).
 * Chỉ dùng cho môi trường local/test; mật khẩu thỏa luật đăng ký (8 ký tự, chữ hoa, chữ thường, số).
 */
class CustomerSeeder extends Seeder
{
    public const EMAIL = 'khach@lavahoa.test';

    public const PASSWORD = 'Lahoa2026';

    public function run(): void
    {
        User::updateOrCreate(['email' => self::EMAIL], [
            'name' => 'Khách Lá & Hoa',
            'phone' => '0901234567',
            'password' => self::PASSWORD,
        ]);
    }
}
