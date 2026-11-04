<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Sản phẩm hoa; giá theo từng size nằm ở product_sizes, bộ ảnh ở product_images.
 */
#[Fillable(['category_id', 'name', 'slug', 'base_price', 'description', 'dimension_info', 'freshness_guarantee', 'stock', 'is_featured', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'stock' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Các dịp gửi thương (categories.type = occasion) qua pivot category_product.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function occasions(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Sắc hoa của sản phẩm (bộ lọc "Sắc hoa bạn thích").
     *
     * @return HasMany<ProductColor, $this>
     */
    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class);
    }

    /**
     * @return HasMany<ProductSize, $this>
     */
    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class)->orderBy('price');
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Size được chọn sẵn khi khách xem (Vừa).
     *
     * @return HasOne<ProductSize, $this>
     */
    public function defaultSize(): HasOne
    {
        return $this->hasOne(ProductSize::class)->where('is_default', true);
    }

    /**
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Giá hiện trên thẻ sản phẩm: giá size mặc định, chưa có size thì dùng base_price.
     *
     * @return Attribute<int, never>
     */
    protected function displayPrice(): Attribute
    {
        return Attribute::get(fn (): int => $this->defaultSize?->price ?? $this->base_price);
    }
}
