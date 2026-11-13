<?php

declare(strict_types=1);

namespace App\Payments;

use App\Enums\PaymentMethod;
use App\Payments\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

/**
 * Chọn cổng thanh toán theo phương thức khách chọn. Danh sách cổng khai báo ở PaymentServiceProvider.
 */
final class PaymentGatewayResolver
{
    /**
     * @param  array<string, PaymentGatewayInterface>  $gateways  payment_method => cổng
     */
    public function __construct(private array $gateways) {}

    public function for(PaymentMethod $method): PaymentGatewayInterface
    {
        return $this->gateways[$method->value]
            ?? throw new InvalidArgumentException("No payment gateway for [{$method->value}].");
    }
}
