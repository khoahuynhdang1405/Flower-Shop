<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Mặc định: giảm 50.000 VNĐ cho đơn từ 500.000 VNĐ.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('LA????##')),
            'discount_type' => DiscountType::Fixed,
            'discount_value' => 50000,
            'min_order_amount' => 500000,
            'max_discount_amount' => null,
            'is_active' => true,
        ];
    }

    public function percentage(int $percent = 10, ?int $max = 100000): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => DiscountType::Percentage,
            'discount_value' => $percent,
            'max_discount_amount' => $max,
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
