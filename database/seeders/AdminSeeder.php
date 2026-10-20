<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;


class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $staffRole = Role::where('slug', 'staff')->first();

        // Tạo tài khoản Admin
        Admin::create([
            'role_id' => $adminRole->id,
            'name' => 'Đăng Khoa',
            'email' => 'admin@lavahoa.com',
            'phone' => '0378599249',
            'password' => Hash::make('password@123'),
            'is_active' => true,
        ]);

        // Tạo tài khoản Thợ cắm hoa
        Admin::create([
            'role_id' => $staffRole->id,
            'name' => 'Nhân viên Cắm hoa',
            'email' => 'staff@lavahoa.com',
            'phone' => '0909888777',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }
}