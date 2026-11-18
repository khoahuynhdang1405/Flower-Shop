<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Email đăng ký nhận chuyện Hoa và ưu đãi (newsletter).
 */
#[Fillable(['email', 'coupon_given'])]
class Subscriber extends Model
{
    /** @use HasFactory<SubscriberFactory> */
    use HasFactory;

    /**
     * Bảng chỉ có created_at.
     */
    public const UPDATED_AT = null;
}
