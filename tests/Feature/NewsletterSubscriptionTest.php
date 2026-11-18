<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendNewsletterWelcomeEmailJob;
use App\Mail\NewsletterWelcomeMail;
use App\Models\Subscriber;
use App\Models\User;
use App\Repositories\Contracts\SubscriberRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Khối "Nhận một chút dịu dàng từ Lá" cuối Trang chủ: lưu email, gửi thư chào kèm mã CHAOHOA.
 */
class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app()->setLocale('vi');
    }

    public function test_home_page_shows_the_newsletter_block_to_guests(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Nhận một chút dịu dàng từ Lá')
            ->assertSeeText('Tặng bạn 10% cho lần gửi yêu thương đầu tiên.')
            ->assertSee('action="'.route('newsletter.store').'"', false)
            ->assertSeeText('Gửi Lá nhé');
    }

    public function test_signed_in_customers_do_not_see_the_newsletter_block(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="newsletter"', false);
    }

    public function test_guest_subscribes_and_is_sent_the_welcome_code(): void
    {
        Bus::fake([SendNewsletterWelcomeEmailJob::class]);

        $this->from(route('home'))
            ->post(route('newsletter.store'), ['email' => '  MinhAnh@Example.com '])
            ->assertRedirect(route('home').'#newsletter')
            ->assertSessionHas('newsletter_status', 'Cảm ơn bạn! Lá đã gửi mã CHAOHOA vào hộp thư của bạn rồi nhé.');

        $subscriber = Subscriber::sole();
        $this->assertSame('minhanh@example.com', $subscriber->email);
        $this->assertSame('CHAOHOA', $subscriber->coupon_given);
        Bus::assertDispatched(
            SendNewsletterWelcomeEmailJob::class,
            fn (SendNewsletterWelcomeEmailJob $job): bool => $job->subscriberId === $subscriber->id && $job->locale === 'vi',
        );
    }

    public function test_thank_you_message_replaces_the_form_after_subscribing(): void
    {
        Bus::fake([SendNewsletterWelcomeEmailJob::class]);

        $this->from(route('home'))->followingRedirects()
            ->post(route('newsletter.store'), ['email' => 'minhanh@example.com'])
            ->assertOk()
            ->assertSeeText('Lá đã gửi mã CHAOHOA vào hộp thư của bạn rồi nhé.')
            ->assertDontSee('action="'.route('newsletter.store').'"', false);
    }

    public function test_existing_subscriber_is_not_duplicated_or_mailed_again(): void
    {
        Bus::fake([SendNewsletterWelcomeEmailJob::class]);
        Subscriber::factory()->create(['email' => 'minhanh@example.com']);

        $this->from(route('home'))
            ->post(route('newsletter.store'), ['email' => 'MinhAnh@example.com'])
            ->assertSessionHas('newsletter_status', 'Email này đã có trong danh sách nhận tin của Lá rồi, bạn xem lại hộp thư để tìm mã CHAOHOA nhé.');

        $this->assertDatabaseCount('subscribers', 1);
        Bus::assertNotDispatched(SendNewsletterWelcomeEmailJob::class);
    }

    public function test_invalid_email_is_rejected_back_to_the_form(): void
    {
        $this->from(route('home'))
            ->post(route('newsletter.store'), ['email' => 'khong-phai-email'])
            ->assertRedirect(route('home').'#newsletter')
            ->assertSessionHasErrorsIn('newsletter', ['email' => 'Email này chưa đúng, bạn kiểm tra lại giúp Lá nhé.']);

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_validation_error_is_shown_inside_the_block(): void
    {
        $this->from(route('home'))->followingRedirects()
            ->post(route('newsletter.store'), ['email' => ''])
            ->assertOk()
            ->assertSee('id="newsletter-error"', false)
            ->assertSeeText('Bạn nhập email để Lá gửi quà nhé.')
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_signed_in_customer_cannot_subscribe_from_the_block(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('newsletter.store'), ['email' => 'minhanh@example.com'])
            ->assertRedirect();

        $this->assertDatabaseCount('subscribers', 0);
    }

    public function test_subscribing_is_rate_limited(): void
    {
        Bus::fake([SendNewsletterWelcomeEmailJob::class]);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('newsletter.store'), ['email' => "khach{$attempt}@example.com"])->assertRedirect();
        }

        $this->post(route('newsletter.store'), ['email' => 'khach6@example.com'])->assertTooManyRequests();
    }

    public function test_job_mails_the_welcome_code_to_the_subscriber(): void
    {
        Mail::fake();
        $subscriber = Subscriber::factory()->create(['email' => 'minhanh@example.com']);

        (new SendNewsletterWelcomeEmailJob($subscriber->id, 'vi'))->handle(app(SubscriberRepositoryInterface::class), app('mail.manager'));

        Mail::assertSent(NewsletterWelcomeMail::class, fn (NewsletterWelcomeMail $mail): bool => $mail->hasTo('minhanh@example.com') && $mail->locale === 'vi');
    }

    public function test_job_for_a_missing_subscriber_sends_nothing(): void
    {
        Mail::fake();

        (new SendNewsletterWelcomeEmailJob(999, 'vi'))->handle(app(SubscriberRepositoryInterface::class), app('mail.manager'));

        Mail::assertNothingSent();
    }

    public function test_welcome_email_shows_the_code_and_its_conditions(): void
    {
        $subscriber = Subscriber::factory()->create(['coupon_given' => 'CHAOHOA']);

        (new NewsletterWelcomeMail($subscriber))
            ->assertHasSubject('Lá gửi bạn chút quà chào')
            ->assertSeeInText('Mã ưu đãi: CHAOHOA')
            ->assertSeeInText('Đơn từ 500.000 VNĐ · Giảm tối đa 100.000 VNĐ')
            ->assertSeeInText('trả lời thư này');
    }

    public function test_newsletter_block_renders_in_english(): void
    {
        app()->setLocale('en');

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('A little tenderness from Lá')
            ->assertSeeText('Send to Lá');
    }
}
