<?php

declare(strict_types=1);

namespace App\DTOs\Customer;

use App\Enums\CustomerGroup;
use Carbon\CarbonImmutable;

/**
 * Thông tin admin được sửa cho một khách (email là danh tính đăng nhập nên không sửa ở đây).
 */
final readonly class UpdateCustomerData
{
    public function __construct(
        public string $name,
        public ?string $phone,
        public ?CarbonImmutable $birthday,
        public CustomerGroup $group,
    ) {}
}
