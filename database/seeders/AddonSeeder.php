<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;

/**
 * Bốn món quà kèm trong thiết kế chi tiết sản phẩm (docs/designs/storefront/04-chi-tiet-san-pham.png).
 */
class AddonSeeder extends Seeder
{
    public function run(): void
    {
        $addons = [
            ['name' => 'Hộp quà nhỏ', 'price' => 80000, 'is_free' => false],
            ['name' => 'Gấu bông êm ái', 'price' => 150000, 'is_free' => false],
            ['name' => 'Chocolate ngọt ngào', 'price' => 120000, 'is_free' => false],
            ['name' => 'Thiệp viết tay', 'price' => 0, 'is_free' => true],
        ];

        foreach ($addons as $addon) {
            Addon::query()->updateOrCreate(['name' => $addon['name']], $addon);
        }
    }
}
