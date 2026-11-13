<?php

declare(strict_types=1);

namespace App\Payments\Gateways;

use App\DTOs\Payment\PaymentRequestData;
use App\DTOs\Payment\PaymentResultData;
use App\Enums\PaymentConfirmationOutcome;
use App\Payments\Contracts\PaymentGatewayInterface;
use App\Payments\PaymentGatewayException;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Response;

/**
 * MoMo Payment Gateway v2 (captureWallet = trang QR quét bằng ví MoMo): gọi API tạo giao dịch, nhận payUrl.
 * Yêu cầu và kết quả đều ký HMAC-SHA256 trên chuỗi "key=value&..." xếp khóa theo chữ cái.
 */
final class MomoGateway implements PaymentGatewayInterface
{
    /** Các trường được ký trong kết quả MoMo gửi về (redirect và IPN). */
    private const RESULT_FIELDS = [
        'amount', 'extraData', 'message', 'orderId', 'orderInfo', 'orderType', 'partnerCode',
        'payType', 'requestId', 'responseTime', 'resultCode', 'transId',
    ];

    public function __construct(
        private HttpClient $http,
        #[Config('services.momo.partner_code')]
        private ?string $partnerCode,
        #[Config('services.momo.access_key')]
        private ?string $accessKey,
        #[Config('services.momo.secret_key')]
        private ?string $secretKey,
        #[Config('services.momo.endpoint')]
        private string $endpoint,
        #[Config('services.momo.request_type')]
        private string $requestType,
    ) {}

    public function createPaymentUrl(PaymentRequestData $request): string
    {
        $fields = [
            'amount' => $request->amount,
            'extraData' => '',
            'ipnUrl' => $request->ipnUrl,
            'orderId' => $request->reference,
            'orderInfo' => $request->description,
            'partnerCode' => (string) $this->partnerCode,
            'redirectUrl' => $request->returnUrl,
            'requestId' => $request->reference,
            'requestType' => $this->requestType,
        ];

        try {
            $response = $this->http->timeout(30)->post($this->endpoint, [
                ...$fields,
                'lang' => 'vi',
                'autoCapture' => true,
                'signature' => $this->sign($fields),
            ]);
        } catch (ConnectionException $exception) {
            throw new PaymentGatewayException('MoMo is unreachable.', previous: $exception);
        }

        $payUrl = $response->json('payUrl');

        if ($response->json('resultCode') !== 0 || ! is_string($payUrl)) {
            throw new PaymentGatewayException('MoMo rejected the payment request: '.$response->json('message', (string) $response->status()));
        }

        return $payUrl;
    }

    public function verify(array $params): PaymentResultData
    {
        $signature = $params['signature'] ?? null;

        foreach (self::RESULT_FIELDS as $field) {
            if (! array_key_exists($field, $params) || ! (is_scalar($params[$field]) || $params[$field] === null)) {
                return PaymentResultData::invalidSignature();
            }
        }

        // Laravel đổi chuỗi rỗng (vd. extraData) thành null; MoMo ký trên chuỗi rỗng.
        $fields = array_map(fn (mixed $value): mixed => $value ?? '', array_intersect_key($params, array_flip(self::RESULT_FIELDS)));

        if (! is_string($signature) || ! hash_equals($this->sign($fields), $signature)) {
            return PaymentResultData::invalidSignature();
        }

        return new PaymentResultData(
            isSignatureValid: true,
            isSuccessful: (string) $params['resultCode'] === '0',
            reference: (string) $params['orderId'],
            amount: (int) $params['amount'],
            transactionRef: (string) $params['transId'],
        );
    }

    /**
     * MoMo chỉ cần HTTP 204 để biết tiệm đã nhận IPN.
     */
    public function ipnResponse(PaymentConfirmationOutcome $outcome): Response
    {
        return new Response(status: 204);
    }

    /**
     * Chữ ký MoMo: thêm accessKey vào các trường, xếp khóa theo chữ cái, nối "key=value" (không urlencode).
     *
     * @param  array<string, mixed>  $fields
     */
    private function sign(array $fields): string
    {
        $fields['accessKey'] = (string) $this->accessKey;
        ksort($fields);

        $data = implode('&', array_map(
            fn (string $key, mixed $value): string => $key.'='.$value,
            array_keys($fields),
            $fields,
        ));

        return hash_hmac('sha256', $data, (string) $this->secretKey);
    }
}
