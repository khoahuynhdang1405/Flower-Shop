<?php

declare(strict_types=1);

namespace Tests\Unit\Payments;

use App\DTOs\Payment\PaymentRequestData;
use App\Enums\PaymentConfirmationOutcome;
use App\Payments\Gateways\MomoGateway;
use App\Payments\PaymentGatewayException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Tạo giao dịch MoMo (API create → payUrl) và kiểm tra chữ ký kết quả trả về (HMAC-SHA256).
 */
class MomoGatewayTest extends TestCase
{
    private const SECRET = 'MOMOSECRET';

    public function test_create_payment_sends_a_signed_capture_wallet_request_and_returns_the_pay_url(): void
    {
        Http::fake(['momo.test/*' => Http::response(['resultCode' => 0, 'payUrl' => 'https://momo.test/pay/abc'])]);

        $url = $this->gateway()->createPaymentUrl($this->request());

        $this->assertSame('https://momo.test/pay/abc', $url);
        Http::assertSent(function (Request $request): bool {
            $expected = hash_hmac('sha256', 'accessKey=ACCESS&amount=760000&extraData=&ipnUrl=https://shop.test/payment/momo/ipn'
                .'&orderId=LH12T1700000000&orderInfo=Thanh toan don hoa LH-8291&partnerCode=MOMOTEST'
                .'&redirectUrl=https://shop.test/payment/momo/return&requestId=LH12T1700000000&requestType=captureWallet', self::SECRET);

            return $request->url() === 'https://momo.test/v2/gateway/api/create'
                && $request['requestType'] === 'captureWallet'
                && $request['amount'] === 760000
                && $request['signature'] === $expected
                && ! isset($request['accessKey']);
        });
    }

    public function test_create_payment_throws_when_momo_rejects_the_request(): void
    {
        Http::fake(['momo.test/*' => Http::response(['resultCode' => 13, 'message' => 'Merchant authentication failed'])]);

        $this->expectException(PaymentGatewayException::class);

        $this->gateway()->createPaymentUrl($this->request());
    }

    public function test_verify_reads_a_successful_payment(): void
    {
        $result = $this->gateway()->verify($this->signed($this->resultParams()));

        $this->assertTrue($result->isSignatureValid);
        $this->assertTrue($result->isSuccessful);
        $this->assertSame('LH12T1700000000', $result->reference);
        $this->assertSame(760000, $result->amount);
        $this->assertSame('4088878653', $result->transactionRef);
    }

    public function test_verify_marks_a_declined_payment_as_unsuccessful(): void
    {
        $result = $this->gateway()->verify($this->signed($this->resultParams(['resultCode' => '1006', 'message' => 'Transaction denied by user.'])));

        $this->assertTrue($result->isSignatureValid);
        $this->assertFalse($result->isSuccessful);
    }

    public function test_verify_rejects_tampered_or_incomplete_data(): void
    {
        $tampered = $this->signed($this->resultParams());
        $tampered['amount'] = '1000';
        $incomplete = $this->signed($this->resultParams());
        unset($incomplete['transId']);

        $this->assertFalse($this->gateway()->verify($tampered)->isSignatureValid);
        $this->assertFalse($this->gateway()->verify($incomplete)->isSignatureValid);
    }

    public function test_ipn_is_acknowledged_with_no_content(): void
    {
        $this->assertSame(204, $this->gateway()->ipnResponse(PaymentConfirmationOutcome::Confirmed)->getStatusCode());
    }

    private function gateway(): MomoGateway
    {
        return new MomoGateway(app(Factory::class), 'MOMOTEST', 'ACCESS', self::SECRET, 'https://momo.test/v2/gateway/api/create', 'captureWallet');
    }

    private function request(): PaymentRequestData
    {
        return new PaymentRequestData(
            reference: 'LH12T1700000000',
            amount: 760000,
            description: 'Thanh toan don hoa LH-8291',
            returnUrl: 'https://shop.test/payment/momo/return',
            ipnUrl: 'https://shop.test/payment/momo/ipn',
            clientIp: '127.0.0.1',
        );
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function resultParams(array $overrides = []): array
    {
        return [
            'partnerCode' => 'MOMOTEST',
            'orderId' => 'LH12T1700000000',
            'requestId' => 'LH12T1700000000',
            'amount' => '760000',
            'orderInfo' => 'Thanh toan don hoa LH-8291',
            'orderType' => 'momo_wallet',
            'transId' => '4088878653',
            'resultCode' => '0',
            'message' => 'Successful.',
            'payType' => 'qr',
            'responseTime' => '1731234567890',
            'extraData' => '',
            ...$overrides,
        ];
    }

    /**
     * @param  array<string, string>  $params
     * @return array<string, string>
     */
    private function signed(array $params): array
    {
        $data = 'accessKey=ACCESS&amount='.$params['amount'].'&extraData='.$params['extraData'].'&message='.$params['message']
            .'&orderId='.$params['orderId'].'&orderInfo='.$params['orderInfo'].'&orderType='.$params['orderType']
            .'&partnerCode='.$params['partnerCode'].'&payType='.$params['payType'].'&requestId='.$params['requestId']
            .'&responseTime='.$params['responseTime'].'&resultCode='.$params['resultCode'].'&transId='.$params['transId'];

        return [...$params, 'signature' => hash_hmac('sha256', $data, self::SECRET)];
    }
}
