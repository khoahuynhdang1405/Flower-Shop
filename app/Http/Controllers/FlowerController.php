<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Product\ListCatalogFilterOptionsAction;
use App\Actions\Product\ListCatalogProductsAction;
use App\Actions\Product\ShowProductDetailAction;
use App\Http\Requests\Storefront\ListFlowersRequest;
use Illuminate\View\View;

/**
 * Trang "Tất cả hoa": cột lọc bên trái + lưới sản phẩm có phân trang.
 */
class FlowerController extends Controller
{
    public function index(
        ListFlowersRequest $request,
        ListCatalogProductsAction $listProducts,
        ListCatalogFilterOptionsAction $listFilterOptions,
    ): View {
        return view('flowers.index', [
            'products' => $listProducts->execute($request->toData())->withQueryString(),
            ...$listFilterOptions->execute(),
        ]);
    }

    public function show(string $slug, ShowProductDetailAction $showProduct): View
    {
        return view('flowers.show', ['product' => $showProduct->execute($slug)]);
    }
}
