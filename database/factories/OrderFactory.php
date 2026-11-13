<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderDeliveryDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Mặc định: đơn COD 680.000 đ, chưa thanh toán, vừa tiếp nhận.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_code' => '#LH-'.fake()->unique()->numberBetween(1000, 9999),
            'subtotal' => 680000,
            'discount_amount' => 0,
            'shipping_fee' => 0,
            'total_amount' => 680000,
            'payment_method' => PaymentMethod::Cod,
            'payment_status' => PaymentStatus::Pending,
        ];
    }

    public function withDeliveryDetail(): static
    {
        return $this->has(OrderDeliveryDetail::factory(), 'deliveryDetail');
    }
}
