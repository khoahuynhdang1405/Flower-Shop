<?php

namespace App\Models;

use App\Enums\CategoryType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Danh mục / bộ sưu tập hoa (Admin › Bộ sưu tập).
 */
#[Fillable(['name', 'slug', 'type', 'description', 'is_active'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'type' => 'occasion',
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Sản phẩm có danh mục chính là danh mục này (products.category_id).
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Sản phẩm được gắn vào bộ sưu tập Theo mùa / Theo dịp qua pivot category_product.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function linkedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * Kèm số sản phẩm (products_count): Kiểu hoa đếm theo products.category_id,
     * Theo mùa / Theo dịp đếm theo pivot category_product.
     *
     * @param  Builder<Category>  $query
     */
    public function scopeWithProductsCount(Builder $query): void
    {
        $flowerType = CategoryType::FlowerType->value;

        if ($query->getQuery()->columns === null) {
            $query->select('categories.*');
        }

        $query->addSelect(DB::raw(
            "case when categories.type = '{$flowerType}'"
            .' then (select count(*) from products where products.category_id = categories.id)'
            .' else (select count(*) from category_product where category_product.category_id = categories.id)'
            .' end as products_count'
        ));
    }

    public function hasProducts(): bool
    {
        return DB::table('products')->where('category_id', $this->id)->exists();
    }
}
