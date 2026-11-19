<?php

declare(strict_types=1);

namespace App\Actions\Collection;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trang "Sản phẩm của bộ sưu tập": sản phẩm đang có, và (với Theo mùa / Theo dịp) danh sách để chọn thêm.
 * Kiểu hoa chỉ xem: sản phẩm thuộc kiểu hoa qua products.category_id, không có danh sách chọn thêm.
 */
final class ShowCollectionProductsAction
{
    public const CANDIDATES_PER_PAGE = 10;

    public function __construct(private ProductRepositoryInterface $products) {}

    /**
     * @return array{
     *     collection: Category,
     *     members: Collection<int, Product>,
     *     candidates: LengthAwarePaginator<int, Product>|null,
     *     search: string|null,
     * }
     */
    public function execute(Category $collection, ?string $search): array
    {
        if (! $collection->type->allowsManualProducts()) {
            return [
                'collection' => $collection,
                'members' => $this->products->findByPrimaryCategory($collection),
                'candidates' => null,
                'search' => null,
            ];
        }

        return [
            'collection' => $collection,
            'members' => $this->products->findLinkedToCollection($collection),
            'candidates' => $this->products->paginateNotLinkedToCollection($collection, $search, self::CANDIDATES_PER_PAGE),
            'search' => $search,
        ];
    }
}
