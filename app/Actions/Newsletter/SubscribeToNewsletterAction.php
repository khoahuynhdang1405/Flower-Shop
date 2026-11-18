<?php

declare(strict_types=1);

namespace App\Actions\Newsletter;

use App\Repositories\Contracts\SubscriberRepositoryInterface;
use Illuminate\Contracts\Config\Repository as Config;

/**
 * Ghi email vào danh sách nhận tin kèm mã chào mừng; chỉ email mới đăng ký lần đầu mới được gửi thư chào.
 * Email so khớp không phân biệt hoa thường và khoảng trắng hai đầu.
 */
final class SubscribeToNewsletterAction
{
    public function __construct(
        private SubscriberRepositoryInterface $subscribers,
        private SendNewsletterWelcomeAction $sendWelcome,
        private Config $config,
    ) {}

    /**
     * @param  string  $locale  ngôn ngữ của thư chào (vi|en)
     * @return bool true nếu là người đăng ký mới, false nếu email đã có trong danh sách
     */
    public function execute(string $email, string $locale): bool
    {
        $subscriber = $this->subscribers->findOrCreateByEmail(
            mb_strtolower(trim($email)),
            $this->config->get('shop.welcome_coupon'),
        );

        if (! $subscriber->wasRecentlyCreated) {
            return false;
        }

        $this->sendWelcome->execute($subscriber, $locale);

        return true;
    }
}
