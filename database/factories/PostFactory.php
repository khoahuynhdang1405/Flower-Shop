<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Mặc định: bài đã đăng, nội dung Markdown ngắn có một tiêu đề mục.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::ucfirst(fake()->unique()->words(5, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(12),
            'content' => "Mở đầu câu chuyện.\n\n## Một điều nhỏ\n\n".fake()->paragraph(),
            'thumbnail' => null,
            'admin_id' => Admin::factory(),
            'is_published' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['is_published' => false]);
    }
}
