<?php

namespace Database\Factories;

use App\Enums\CartItemType;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Mặc định: một bó hoa cỡ vừa 680.000 đ.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'item_type' => CartItemType::Flower,
            'item_id' => 1,
            'item_name' => 'Lời thì thầm',
            'size_name' => 'Cỡ vừa',
            'unit_price' => 680000,
            'quantity' => 1,
            'total_price' => 680000,
        ];
    }
}
