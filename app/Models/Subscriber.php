<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Email đăng ký nhận chuyện Hoa và ưu đãi (newsletter).
 */
#[Fillable(['email', 'coupon_given'])]
class Subscriber extends Model
{
    /**
     * Bảng chỉ có created_at.
     */
    public const UPDATED_AT = null;
}
