<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Addon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Addon>
 */
class AddonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::ucfirst(fake()->words(2, true)),
            'price' => fake()->randomElement([80000, 120000, 150000]),
            'image_path' => null,
            'is_free' => false,
            'is_active' => true,
        ];
    }

    /**
     * Quà miễn phí (thiệp viết tay).
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => ['price' => 0, 'is_free' => true]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
