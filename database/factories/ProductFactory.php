<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name),
            'base_price' => fake()->randomElement([450000, 550000, 690000, 890000]),
            'description' => fake()->sentence(),
            'stock' => fake()->numberBetween(5, 20),
            'is_featured' => false,
            'is_active' => true,
        ];
    }
}
