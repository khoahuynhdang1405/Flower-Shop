<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderDeliveryDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Người đặt, người nhận, lịch giao và thiệp của một đơn (1–1 với orders, không có timestamps).
 */
#[Fillable([
    'order_id', 'sender_name', 'sender_phone', 'sender_email', 'is_gift', 'receiver_name', 'receiver_phone',
    'shipping_address', 'shipping_district', 'delivery_date', 'time_slot', 'card_sender', 'card_receiver',
    'card_message', 'delivery_notes',
])]
class OrderDeliveryDetail extends Model
{
    /** @use HasFactory<OrderDeliveryDetailFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_gift' => 'boolean',
            'delivery_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
