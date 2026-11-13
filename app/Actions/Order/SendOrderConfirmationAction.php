<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Jobs\SendOrderConfirmationEmailJob;
use App\Models\Order;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * Xếp hàng email xác nhận đơn, chỉ chạy sau khi transaction đang mở (nếu có) đã commit.
 */
final class SendOrderConfirmationAction
{
    public function __construct(private Dispatcher $bus) {}

    public function execute(Order $order): void
    {
        $this->bus->dispatch((new SendOrderConfirmationEmailJob($order->id))->afterCommit());
    }
}
