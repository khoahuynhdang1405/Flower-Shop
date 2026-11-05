<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Product\CatalogFilterData;
use App\Enums\CategoryType;
use App\Enums\FlowerColor;
use App\Enums\PriceRange;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ProductRepository implements ProductRepositoryInterface
{
    public function loadSizesAndImages(Product $product): Product
    {
        return $product->load(['sizes', 'images.size', 'colors']);
    }

    public function replaceColors(Product $product, array $colors): void
    {
        $product->colors()->delete();
        $product->colors()->createMany(array_map(fn (FlowerColor $color): array => ['color' => $color], $colors));
    }

    public function findFeaturedForSale(int $limit): Collection
    {
        return Product::query()
            ->where('is_featured', true)
            ->where('is_active', true)
            ->with(['category', 'defaultSize', 'primaryImage'])
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function paginateForCatalog(CatalogFilterData $filters, int $perPage): LengthAwarePaginator
    {
        return Product::query()
            ->where('is_active', true)
            ->when($filters->typeSlugs !== [], fn (Builder $query) => $query->whereHas('category', fn (Builder $category) => $category
                ->where('type', CategoryType::FlowerType)
                ->whereIn('slug', $filters->typeSlugs)))
            ->when($filters->occasionSlugs !== [], fn (Builder $query) => $query->whereHas('occasions', fn (Builder $occasion) => $occasion
                ->where('type', CategoryType::Occasion)
                ->whereIn('slug', $filters->occasionSlugs)))
            ->when($filters->colors !== [], fn (Builder $query) => $query->whereHas('colors', fn (Builder $color) => $color
                ->whereIn('color', array_map(fn (FlowerColor $chosen): string => $chosen->value, $filters->colors))))
            ->when($filters->priceRange, fn (Builder $query, PriceRange $range) => $this->whereDisplayPriceIn($query, $range))
            ->with(['category', 'defaultSize', 'primaryImage'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForSaleBySlug(string $slug): ?Product
    {
        return Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'sizes', 'images'])
            ->first();
    }

    /**
     * Giá hiển thị = giá size mặc định; sản phẩm chưa có size mặc định thì so bằng base_price.
     *
     * @param  Builder<Product>  $query
     */
    private function whereDisplayPriceIn(Builder $query, PriceRange $range): void
    {
        $query->where(fn (Builder $price) => $price
            ->whereHas('defaultSize', fn (Builder $size) => $this->whereInRange($size, 'price', $range))
            ->orWhere(fn (Builder $withoutSize) => $this->whereInRange($withoutSize->whereDoesntHave('defaultSize'), 'base_price', $range)));
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function whereInRange(Builder $query, string $column, PriceRange $range): Builder
    {
        return $query
            ->when($range->min() !== null, fn (Builder $bounded) => $bounded->where($column, '>=', $range->min()))
            ->when($range->max() !== null, fn (Builder $bounded) => $bounded->where($column, '<', $range->max()));
    }
}
