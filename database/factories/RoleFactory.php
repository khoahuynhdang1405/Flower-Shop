<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Quản trị viên',
            'slug' => 'admin',
        ];
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Nhân viên',
            'slug' => 'staff',
        ]);
    }
}
