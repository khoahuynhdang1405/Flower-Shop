<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolepermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo vai trò
        $adminRole = Role::create([
            'name' => 'Chủ tiệm',
            'slug' => 'admin',
            'description' => 'Quản trị viên tối cao, toàn quyền hệ thống'
        ]);

        $staffRole = Role::create([
            'name' => 'Thợ cắm hoa',
            'slug' => 'staff',
            'description' => 'Nhân viên cắm hoa và sử lý đơn hàng'
        ]);

        // Các quyền hạng
        $permProducts = Permission::create([
            'name' => 'Quản lý sản phẩm',
            'slug' => 'manage-products',
            'group_name' => 'Sản phẩm'
        ]);

        $permOrders = Permission::create([
            'name' => 'Quản lý đơn hàng',
            'slug' => 'manage-orders',
            'group_name' => 'Đơn hàng'
        ]);

        // Gán quyền cho vai trò
        $adminRole->permissions()->attach([$permProducts->id, $permOrders->id]);
        $staffRole->permissions()->attach([$permOrders->id]);
    }
}