<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name),
            'type' => CategoryType::Occasion,
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function season(): static
    {
        return $this->state(fn (array $attributes) => ['type' => CategoryType::Season]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
