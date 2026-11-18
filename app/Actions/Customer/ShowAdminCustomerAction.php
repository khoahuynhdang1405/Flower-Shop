<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Trang chi tiết khách: số liệu đơn và 10 đơn gần nhất.
 */
final class ShowAdminCustomerAction
{
    public const RECENT_ORDERS = 10;

    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(User $customer): User
    {
        return $this->users->loadOrderHistory($customer, self::RECENT_ORDERS);
    }
}
