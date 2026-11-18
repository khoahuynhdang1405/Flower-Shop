<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Subscriber;

interface SubscriberRepositoryInterface
{
    public function findById(int $id): ?Subscriber;

    /**
     * Lấy người đăng ký theo email, chưa có thì tạo kèm mã chào mừng.
     * Bản ghi vừa tạo có wasRecentlyCreated = true; an toàn khi hai yêu cầu cùng email đến một lúc.
     */
    public function findOrCreateByEmail(string $email, string $couponCode): Subscriber;
}
