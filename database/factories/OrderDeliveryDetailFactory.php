<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderDeliveryDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderDeliveryDetail>
 */
class OrderDeliveryDetailFactory extends Factory
{
    /**
     * Mặc định: gửi tặng, giao ngày mai khung 14:00-16:00.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'sender_name' => 'Huỳnh Đăng Khoa',
            'sender_phone' => '0903456789',
            'sender_email' => fake()->safeEmail(),
            'is_gift' => true,
            'receiver_name' => 'Nguyễn Ngọc Đoan Trang',
            'receiver_phone' => '0912345678',
            'shipping_address' => '18 Võ Văn Tần, Phường 6',
            'shipping_district' => 'Quận 3',
            'delivery_date' => now()->addDay()->toDateString(),
            'time_slot' => '14:00-16:00',
            'card_sender' => 'Đăng Khoa',
            'card_receiver' => 'Đoan Trang',
            'card_message' => 'Chúc Trang một ngày thật dịu dàng.',
            'delivery_notes' => null,
        ];
    }
}
