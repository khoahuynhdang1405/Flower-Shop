<?php

declare(strict_types=1);

namespace App\Actions\Category;

use App\Enums\Season;
use App\Repositories\Contracts\CategoryRepositoryInterface;

/**
 * Ảnh chính của 4 bộ sưu tập mùa (banner mùa, thẻ mùa). Mùa chưa có bộ sưu tập đang hiển thị
 * hoặc chưa có ảnh thì null, giao diện dùng ảnh tĩnh.
 */
final class ListSeasonCoversAction
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    /**
     * @return array<string, string|null> giá trị Season => URL ảnh chính
     */
    public function execute(): array
    {
        $collections = $this->categories->findActiveSeasonsBySlugs(
            array_map(fn (Season $season): string => $season->collectionSlug(), Season::cases()),
        );

        $covers = [];

        foreach (Season::cases() as $season) {
            $covers[$season->value] = $collections->get($season->collectionSlug())?->cover_url;
        }

        return $covers;
    }
}
