<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Email "Đặt hoa thành công" gửi người đặt: COD ngay khi đặt, VNPAY/MoMo khi đã thanh toán.
 * Không tự xếp hàng — SendOrderConfirmationEmailJob lo phần queue.
 */
final class OrderConfirmationMail extends Mailable
{
    /**
     * @param  Order  $order  kèm deliveryDetail, items, promotion
     */
    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('order.mail.subject', ['code' => $this->order->order_code]),
            replyTo: [new Address(config('shop.email'), config('app.name'))],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.orders.confirmation');
    }
}
