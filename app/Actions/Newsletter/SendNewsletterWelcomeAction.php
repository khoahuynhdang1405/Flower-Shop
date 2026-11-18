<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Jobs\SendNewsletterWelcomeEmailJob;
use App\Models\Subscriber;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * Xếp hàng thư chào kèm mã ưu đãi, chỉ chạy sau khi transaction đang mở (nếu có) đã commit.
 */
final class SendNewsletterWelcomeAction
{
    public function __construct(private Dispatcher $bus) {}

    public function execute(Subscriber $subscriber, string $locale): void
    {
        $this->bus->dispatch((new SendNewsletterWelcomeEmailJob($subscriber->id, $locale))->afterCommit());
    }
}
