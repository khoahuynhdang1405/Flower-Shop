<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class CategoryRepository implements CategoryRepositoryInterface
{
    public function findActiveFlowerTypesWithProductCount(): Collection
    {
        return Category::query()
            ->where('type', CategoryType::FlowerType)
            ->where('is_active', true)
            ->withCount(['products' => fn (Builder $products) => $products->where('is_active', true)])
            ->orderBy('name')
            ->get();
    }

    public function findActiveOccasions(): Collection
    {
        return Category::query()
            ->where('type', CategoryType::Occasion)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }

    public function attachProducts(Category $collection, array $productIds): int
    {
        return count($collection->linkedProducts()->syncWithoutDetaching($productIds)['attached']);
    }

    public function detachProduct(Category $collection, Product $product): bool
    {
        return $collection->linkedProducts()->detach($product->id) > 0;
    }

    public function create(array $attributes): Category
    {
        return Category::create($attributes);
    }

    public function update(Category $collection, array $attributes): void
    {
        $collection->update($attributes);
    }

    public function delete(Category $collection): void
    {
        $collection->delete();
    }

    public function hasPrimaryProducts(Category $collection): bool
    {
        return $collection->products()->exists();
    }

    public function findActiveSeasonsBySlugs(array $slugs): Collection
    {
        return Category::query()
            ->where('type', CategoryType::Season)
            ->where('is_active', true)
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');
    }
}
