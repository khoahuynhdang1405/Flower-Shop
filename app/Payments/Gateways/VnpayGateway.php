<?php

declare(strict_types=1);

namespace App\Payments\Gateways;

use App\DTOs\Payment\PaymentRequestData;
use App\DTOs\Payment\PaymentResultData;
use App\Enums\PaymentConfirmationOutcome;
use App\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

/**
 * VNPAY Payment Gateway v2.1.0: chuyển khách sang URL đã ký HMAC-SHA512.
 * Có vnp_BankCode thì chỉ mở phương thức đó (VD: VNPAYQR); không có thì khách tự chọn trên trang VNPAY.
 * Số tiền gửi/nhận nhân 100; thời gian theo GMT+7.
 */
final class VnpayGateway implements PaymentGatewayInterface
{
    private const TIMEZONE = 'Asia/Ho_Chi_Minh';

    public function __construct(
        #[Config('services.vnpay.tmn_code')]
        private ?string $tmnCode,
        #[Config('services.vnpay.hash_secret')]
        private ?string $hashSecret,
        #[Config('services.vnpay.url')]
        private string $url,
        #[Config('services.vnpay.version')]
        private string $version,
        #[Config('services.vnpay.bank_code')]
        private ?string $bankCode,
        #[Config('services.vnpay.expire_minutes')]
        private int $expireMinutes,
    ) {}

    public function createPaymentUrl(PaymentRequestData $request): string
    {
        $now = Carbon::now(self::TIMEZONE);

        $query = $this->signingData(array_filter([
            'vnp_Version' => $this->version,
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => (string) $this->tmnCode,
            'vnp_Amount' => $request->amount * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_BankCode' => $this->bankCode,
            'vnp_TxnRef' => $request->reference,
            'vnp_OrderInfo' => $request->description,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $request->returnUrl,
            'vnp_IpAddr' => $request->clientIp,
            'vnp_CreateDate' => $now->format('YmdHis'),
            'vnp_ExpireDate' => $now->copy()->addMinutes($this->expireMinutes)->format('YmdHis'),
        ], fn (mixed $value): bool => $value !== null));

        return $this->url.'?'.$query.'&vnp_SecureHash='.$this->sign($query);
    }

    public function verify(array $params): PaymentResultData
    {
        $signature = $params['vnp_SecureHash'] ?? null;
        // Laravel đổi chuỗi rỗng thành null; VNPAY ký cả tham số rỗng ("vnp_BankTranNo=").
        $fields = array_map(fn (mixed $value): mixed => $value ?? '', array_filter(
            $params,
            fn (mixed $value, string|int $key): bool => is_string($key) && str_starts_with($key, 'vnp_')
                && ! in_array($key, ['vnp_SecureHash', 'vnp_SecureHashType'], true),
            ARRAY_FILTER_USE_BOTH,
        ));

        if (! is_string($signature) || ! isset($fields['vnp_TxnRef'], $fields['vnp_Amount'])
            || ! hash_equals($this->sign($this->signingData($fields)), strtolower($signature))) {
            return PaymentResultData::invalidSignature();
        }

        return new PaymentResultData(
            isSignatureValid: true,
            isSuccessful: ($fields['vnp_ResponseCode'] ?? null) === '00' && ($fields['vnp_TransactionStatus'] ?? null) === '00',
            reference: (string) $fields['vnp_TxnRef'],
            amount: intdiv((int) $fields['vnp_Amount'], 100),
            transactionRef: isset($fields['vnp_TransactionNo']) ? (string) $fields['vnp_TransactionNo'] : null,
        );
    }

    /**
     * VNPAY chờ JSON {RspCode, Message}; RspCode khác "00"/"02" thì VNPAY gửi lại IPN.
     */
    public function ipnResponse(PaymentConfirmationOutcome $outcome): JsonResponse
    {
        [$code, $message] = match ($outcome) {
            PaymentConfirmationOutcome::Confirmed => ['00', 'Confirm Success'],
            PaymentConfirmationOutcome::AlreadyConfirmed => ['02', 'Order already confirmed'],
            PaymentConfirmationOutcome::OrderNotFound => ['01', 'Order not found'],
            PaymentConfirmationOutcome::InvalidAmount => ['04', 'Invalid amount'],
            PaymentConfirmationOutcome::InvalidSignature => ['97', 'Invalid signature'],
        };

        return new JsonResponse(['RspCode' => $code, 'Message' => $message]);
    }

    /**
     * Chuỗi ký: tham số sắp xếp theo tên, urlencode từng khóa/giá trị (khoảng trắng thành "+").
     *
     * @param  array<string, mixed>  $params
     */
    private function signingData(array $params): string
    {
        ksort($params);

        return http_build_query($params);
    }

    private function sign(string $data): string
    {
        return hash_hmac('sha512', $data, (string) $this->hashSecret);
    }
}
