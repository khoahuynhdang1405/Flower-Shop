<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\DTOs\Customer\UpdateCustomerData;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * Admin sửa tên, SĐT, ngày sinh và nhóm khách của một khách.
 */
final class UpdateCustomerAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    public function execute(User $customer, UpdateCustomerData $data): void
    {
        $this->users->update($customer, [
            'name' => $data->name,
            'phone' => $data->phone,
            'birthday' => $data->birthday?->toDateString(),
            'customer_group' => $data->group,
        ]);
    }
}
