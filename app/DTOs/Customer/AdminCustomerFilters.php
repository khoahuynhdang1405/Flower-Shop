<?php

declare(strict_types=1);

namespace App\DTOs\Customer;

use App\Enums\CustomerGroup;
use App\Enums\CustomerSort;

/**
 * Bộ lọc danh sách khách admin. $group null = "Tất cả nhóm".
 */
final readonly class AdminCustomerFilters
{
    public function __construct(
        public ?string $search = null,
        public ?CustomerGroup $group = null,
        public CustomerSort $sort = CustomerSort::RecentlyUpdated,
    ) {}
}
