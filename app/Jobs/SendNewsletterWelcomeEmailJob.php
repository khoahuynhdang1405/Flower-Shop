<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\NewsletterWelcomeMail;
use App\Repositories\Contracts\SubscriberRepositoryInterface;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Gửi thư chào người đăng ký nhận tin ở nền, kèm mã ưu đãi đã ghi trong subscribers.coupon_given.
 */
final class SendNewsletterWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300];

    public function __construct(public int $subscriberId, public string $locale) {}

    public function handle(SubscriberRepositoryInterface $subscribers, MailFactory $mail): void
    {
        $subscriber = $subscribers->findById($this->subscriberId);

        if ($subscriber === null || $subscriber->coupon_given === null) {
            return;
        }

        $mail->mailer()->to($subscriber->email)
            ->locale($this->locale)
            ->send(new NewsletterWelcomeMail($subscriber));
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Newsletter welcome email failed', ['subscriber_id' => $this->subscriberId, 'error' => $exception?->getMessage()]);
    }
}
