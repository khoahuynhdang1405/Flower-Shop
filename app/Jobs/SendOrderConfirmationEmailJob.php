<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\OrderConfirmationMail;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Gửi email xác nhận đơn ở nền. Chỉ giữ id đơn, lúc chạy mới đọc lại đơn mới nhất từ DB.
 */
final class SendOrderConfirmationEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300];

    public function __construct(public int $orderId) {}

    public function handle(OrderRepositoryInterface $orders, MailFactory $mail): void
    {
        $order = $orders->findWithDetailsById($this->orderId);

        if ($order === null) {
            return;
        }

        $mail->mailer()->to($order->deliveryDetail->sender_email, $order->deliveryDetail->sender_name)
            ->locale('vi')
            ->send(new OrderConfirmationMail($order));
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Order confirmation email failed', ['order_id' => $this->orderId, 'error' => $exception?->getMessage()]);
    }
}
