<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\Newsletter\SendNewsletterWelcomeAction;
use App\Actions\Newsletter\SubscribeToNewsletterAction;
use App\Jobs\SendNewsletterWelcomeEmailJob;
use App\Models\Subscriber;
use App\Repositories\Contracts\SubscriberRepositoryInterface;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Bus\Dispatcher;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Đăng ký nhận tin với repository giả: chuẩn hóa email, chỉ gửi thư chào cho người mới.
 */
class SubscribeToNewsletterActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private SubscriberRepositoryInterface&MockInterface $subscribers;

    private Dispatcher&MockInterface $bus;

    private SubscribeToNewsletterAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscribers = Mockery::mock(SubscriberRepositoryInterface::class);
        $this->bus = Mockery::mock(Dispatcher::class);
        $this->action = new SubscribeToNewsletterAction(
            $this->subscribers,
            new SendNewsletterWelcomeAction($this->bus),
            new Config(['shop' => ['welcome_coupon' => 'CHAOHOA']]),
        );
    }

    public function test_new_subscriber_gets_the_welcome_code_and_email(): void
    {
        $subscriber = $this->subscriber(id: 7, wasRecentlyCreated: true);
        $this->subscribers->expects('findOrCreateByEmail')->with('minhanh@example.com', 'CHAOHOA')->andReturn($subscriber);
        $this->bus->expects('dispatch')->withArgs(
            fn (SendNewsletterWelcomeEmailJob $job): bool => $job->subscriberId === 7 && $job->locale === 'en',
        );

        $this->assertTrue($this->action->execute('  MinhAnh@Example.COM ', 'en'));
    }

    public function test_existing_subscriber_is_not_emailed_again(): void
    {
        $this->subscribers->expects('findOrCreateByEmail')->andReturn($this->subscriber(id: 7, wasRecentlyCreated: false));
        $this->bus->expects('dispatch')->never();

        $this->assertFalse($this->action->execute('minhanh@example.com', 'vi'));
    }

    private function subscriber(int $id, bool $wasRecentlyCreated): Subscriber
    {
        $subscriber = new Subscriber;
        $subscriber->id = $id;
        $subscriber->wasRecentlyCreated = $wasRecentlyCreated;

        return $subscriber;
    }
}
