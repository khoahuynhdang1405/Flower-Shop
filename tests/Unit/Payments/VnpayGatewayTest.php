<?php

declare(strict_types=1);

namespace Tests\Unit\Payments;

use App\DTOs\Payment\PaymentRequestData;
use App\Enums\PaymentConfirmationOutcome;
use App\Payments\Gateways\VnpayGateway;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * URL thanh toán VNPAY và kiểm tra chữ ký kết quả trả về (HMAC-SHA512).
 */
class VnpayGatewayTest extends TestCase
{
    private const SECRET = 'TESTSECRET';

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_payment_url_is_signed_and_opens_qr_payment_for_the_order_amount(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 10, 9, 30, 0, 'Asia/Ho_Chi_Minh'));

        $url = $this->gateway()->createPaymentUrl($this->request());

        $this->assertStringStartsWith('https://sandbox.test/pay?', $url);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

        $this->assertSame('76000000', $params['vnp_Amount']);
        $this->assertSame('VNPAYQR', $params['vnp_BankCode']);
        $this->assertSame('LH12T1700000000', $params['vnp_TxnRef']);
        $this->assertSame('TMNTEST', $params['vnp_TmnCode']);
        $this->assertSame('20261110093000', $params['vnp_CreateDate']);
        $this->assertSame('20261110094500', $params['vnp_ExpireDate']);

        $signed = $params;
        unset($signed['vnp_SecureHash']);
        ksort($signed);
        $this->assertSame(hash_hmac('sha512', http_build_query($signed), self::SECRET), $params['vnp_SecureHash']);
    }

    public function test_without_a_bank_code_the_customer_picks_the_method_on_vnpay(): void
    {
        $gateway = new VnpayGateway('TMNTEST', self::SECRET, 'https://sandbox.test/pay', '2.1.0', null, 15);

        parse_str((string) parse_url($gateway->createPaymentUrl($this->request()), PHP_URL_QUERY), $params);

        $this->assertArrayNotHasKey('vnp_BankCode', $params);
        $signed = $params;
        unset($signed['vnp_SecureHash']);
        ksort($signed);
        $this->assertSame(hash_hmac('sha512', http_build_query($signed), self::SECRET), $params['vnp_SecureHash']);
    }

    public function test_verify_reads_a_successful_payment(): void
    {
        $result = $this->gateway()->verify($this->signed($this->returnParams()));

        $this->assertTrue($result->isSignatureValid);
        $this->assertTrue($result->isSuccessful);
        $this->assertSame('LH12T1700000000', $result->reference);
        $this->assertSame(760000, $result->amount);
        $this->assertSame('14226112', $result->transactionRef);
    }

    public function test_verify_accepts_empty_params_that_laravel_turned_into_null(): void
    {
        $params = $this->signed($this->returnParams(['vnp_BankTranNo' => '']));
        $params['vnp_BankTranNo'] = null;

        $this->assertTrue($this->gateway()->verify($params)->isSignatureValid);
    }

    public function test_verify_marks_a_cancelled_payment_as_unsuccessful(): void
    {
        $result = $this->gateway()->verify($this->signed($this->returnParams(['vnp_ResponseCode' => '24', 'vnp_TransactionStatus' => '02'])));

        $this->assertTrue($result->isSignatureValid);
        $this->assertFalse($result->isSuccessful);
    }

    public function test_verify_rejects_tampered_or_unsigned_data(): void
    {
        $tampered = $this->signed($this->returnParams());
        $tampered['vnp_Amount'] = '100';

        $this->assertFalse($this->gateway()->verify($tampered)->isSignatureValid);
        $this->assertFalse($this->gateway()->verify($this->returnParams())->isSignatureValid);
        $this->assertFalse($this->gateway()->verify([])->isSignatureValid);
    }

    public function test_ipn_response_uses_vnpay_codes(): void
    {
        $this->assertSame(['RspCode' => '00', 'Message' => 'Confirm Success'], $this->gateway()->ipnResponse(PaymentConfirmationOutcome::Confirmed)->getData(true));
        $this->assertSame('02', $this->gateway()->ipnResponse(PaymentConfirmationOutcome::AlreadyConfirmed)->getData(true)['RspCode']);
        $this->assertSame('01', $this->gateway()->ipnResponse(PaymentConfirmationOutcome::OrderNotFound)->getData(true)['RspCode']);
        $this->assertSame('04', $this->gateway()->ipnResponse(PaymentConfirmationOutcome::InvalidAmount)->getData(true)['RspCode']);
        $this->assertSame('97', $this->gateway()->ipnResponse(PaymentConfirmationOutcome::InvalidSignature)->getData(true)['RspCode']);
    }

    private function gateway(): VnpayGateway
    {
        return new VnpayGateway('TMNTEST', self::SECRET, 'https://sandbox.test/pay', '2.1.0', 'VNPAYQR', 15);
    }

    private function request(): PaymentRequestData
    {
        return new PaymentRequestData(
            reference: 'LH12T1700000000',
            amount: 760000,
            description: 'Thanh toan don hoa LH-8291',
            returnUrl: 'https://shop.test/payment/vnpay/return',
            ipnUrl: 'https://shop.test/payment/vnpay/ipn',
            clientIp: '127.0.0.1',
        );
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function returnParams(array $overrides = []): array
    {
        return [
            'vnp_Amount' => '76000000',
            'vnp_BankCode' => 'VNPAYQR',
            'vnp_OrderInfo' => 'Thanh toan don hoa LH-8291',
            'vnp_ResponseCode' => '00',
            'vnp_TmnCode' => 'TMNTEST',
            'vnp_TransactionNo' => '14226112',
            'vnp_TransactionStatus' => '00',
            'vnp_TxnRef' => 'LH12T1700000000',
            ...$overrides,
        ];
    }

    /**
     * @param  array<string, string>  $params
     * @return array<string, string>
     */
    private function signed(array $params): array
    {
        ksort($params);

        return [...$params, 'vnp_SecureHashType' => 'SHA512', 'vnp_SecureHash' => hash_hmac('sha512', http_build_query($params), self::SECRET)];
    }
}
