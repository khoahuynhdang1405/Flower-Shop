<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Auth\LoginRequest as CustomerLoginRequest;

/**
 * Đăng nhập trang quản trị: cùng luật nhập + khóa tạm như khách, nhưng qua guard `admin`
 * và chỉ cho tài khoản đang hoạt động (admins.is_active).
 */
class LoginRequest extends CustomerLoginRequest
{
    protected function guardName(): string
    {
        return 'admin';
    }

    /**
     * @return array<string, mixed>
     */
    protected function credentials(): array
    {
        return [...parent::credentials(), 'is_active' => true];
    }
}
