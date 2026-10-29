<?php

namespace App\Models;

use App\Enums\CategoryType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
     * Kèm số sản phẩm (products_count). Chưa có model Product nên đếm thẳng trên bảng products.
     *
     * @param  Builder<Category>  $query
     */
    public function scopeWithProductsCount(Builder $query): void
    {
        $query->addSelect([
            'products_count' => DB::table('products')->selectRaw('count(*)')->whereColumn('products.category_id', 'categories.id'),
        ]);
    }

    public function hasProducts(): bool
    {
        return DB::table('products')->where('category_id', $this->id)->exists();
    }
}
