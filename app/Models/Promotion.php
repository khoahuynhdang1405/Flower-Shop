<?php

namespace App\Models;

use App\Enums\DiscountType;
use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Mã giảm giá (Admin › Ưu đãi). Bảng promotions không có timestamps.
 */
#[Fillable(['code', 'discount_type', 'discount_value', 'min_order_amount', 'max_discount_amount', 'is_active'])]
class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'min_order_amount' => 0,
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_type' => DiscountType::class,
            'discount_value' => 'integer',
            'min_order_amount' => 'integer',
            'max_discount_amount' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kèm số đơn đã dùng mã (orders_count). Chưa có model Order nên đếm thẳng trên bảng orders.
     *
     * @param  Builder<Promotion>  $query
     */
    public function scopeWithOrdersCount(Builder $query): void
    {
        $query->addSelect([
            'orders_count' => DB::table('orders')->selectRaw('count(*)')->whereColumn('orders.promotion_id', 'promotions.id'),
        ]);
    }

    public function hasOrders(): bool
    {
        return DB::table('orders')->where('promotion_id', $this->id)->exists();
    }
}
