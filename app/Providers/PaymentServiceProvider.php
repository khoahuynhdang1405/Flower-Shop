<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\PaymentMethod;
use App\Payments\Gateways\MomoGateway;
use App\Payments\Gateways\VnpayGateway;
use App\Payments\PaymentGatewayResolver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Đăng ký các cổng thanh toán trực tuyến (cấu hình ở config/services.php).
 */
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGatewayResolver::class, fn (Application $app): PaymentGatewayResolver => new PaymentGatewayResolver([
            PaymentMethod::Vnpay->value => $app->make(VnpayGateway::class),
            PaymentMethod::Momo->value => $app->make(MomoGateway::class),
        ]));
    }
}
