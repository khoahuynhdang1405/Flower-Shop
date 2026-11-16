<?php

declare(strict_types=1);

namespace App\DTOs\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;

/**
 * Bộ lọc danh sách đơn admin. $tab null = tab "Tất cả".
 */
final readonly class AdminOrderFilters
{
    public function __construct(
        public ?OrderStatus $tab = null,
        public ?string $search = null,
        public ?PaymentStatus $payment = null,
    ) {}
}
