<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CartItemType;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một bó hoa hoặc quà kèm trong đơn: tên, size, đơn giá là snapshot lúc đặt.
 * item_id trỏ products.id hoặc addons.id tùy item_type (không có khóa ngoại).
 */
#[Fillable(['order_id', 'item_type', 'item_id', 'item_name', 'size_name', 'unit_price', 'quantity', 'total_price'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item_type' => CartItemType::class,
            'unit_price' => 'integer',
            'quantity' => 'integer',
            'total_price' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Sản phẩm gốc của dòng hoa (để lấy ảnh). Chỉ đúng với item_type = flower; null nếu sản phẩm đã bị xóa.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }
}
