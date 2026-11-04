<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FlowerColor;
use Database\Factories\ProductColorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Một sắc hoa của sản phẩm (khóa chính ghép product_id + color).
 */
#[Fillable(['product_id', 'color'])]
class ProductColor extends Model
{
    /** @use HasFactory<ProductColorFactory> */
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'color' => FlowerColor::class,
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
