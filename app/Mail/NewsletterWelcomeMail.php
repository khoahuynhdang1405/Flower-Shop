<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Subscriber;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Thư chào người vừa đăng ký nhận tin, kèm mã ưu đãi cho đơn đầu tiên.
 * Không tự xếp hàng — SendNewsletterWelcomeEmailJob lo phần queue.
 */
final class NewsletterWelcomeMail extends Mailable
{
    public function __construct(public Subscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('newsletter.mail.subject'),
            replyTo: [new Address(config('shop.email'), config('app.name'))],
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.newsletter.welcome');
    }
}
