<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\CategoryType;
use App\Models\Category;
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
}
