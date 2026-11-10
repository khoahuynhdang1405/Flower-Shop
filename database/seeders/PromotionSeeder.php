<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DiscountType;
use App\Models\Promotion;
use Illuminate\Database\Seeder;

/**
 * Mã chào mừng CHAOHOA (docs/design-vs-db-gaps.md #5): giảm 10% cho đơn từ 500.000 VNĐ, tối đa 100.000 VNĐ.
 * Chạy lại không tạo trùng — mã đã có thì cập nhật về đúng thông số này.
 */
class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        Promotion::query()->updateOrCreate(['code' => 'CHAOHOA'], [
            'discount_type' => DiscountType::Percentage,
            'discount_value' => 10,
            'min_order_amount' => 500000,
            'max_discount_amount' => 100000,
            'is_active' => true,
        ]);
    }
}
