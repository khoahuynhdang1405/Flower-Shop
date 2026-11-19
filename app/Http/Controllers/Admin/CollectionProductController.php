<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Collection\AddProductsToCollectionAction;
use App\Actions\Collection\RemoveProductFromCollectionAction;
use App\Actions\Collection\ShowCollectionProductsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddCollectionProductsRequest;
use App\Http\Requests\Admin\ListCollectionProductsRequest;
use App\Http\Requests\Admin\RemoveCollectionProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Bộ sưu tập › Sản phẩm: xem sản phẩm của bộ sưu tập, thêm/bỏ với bộ sưu tập Theo mùa / Theo dịp.
 */
class CollectionProductController extends Controller
{
    public function index(ListCollectionProductsRequest $request, Category $collection, ShowCollectionProductsAction $showProducts): View
    {
        return view('admin.collections.products', $showProducts->execute($collection, $request->search()));
    }

    public function store(AddCollectionProductsRequest $request, Category $collection, AddProductsToCollectionAction $addProducts): RedirectResponse
    {
        $added = $addProducts->execute($collection, $request->productIds());
        $message = $added > 0
            ? __('admin.collection_products.flash.added', ['count' => $added, 'name' => $collection->name])
            : __('admin.collection_products.flash.already_added', ['name' => $collection->name]);

        return redirect()->route('admin.collections.products', $collection)->with('status', $message);
    }

    public function destroy(RemoveCollectionProductRequest $request, Category $collection, Product $product, RemoveProductFromCollectionAction $removeProduct): RedirectResponse
    {
        $removeProduct->execute($collection, $product);

        return back()->with('status', __('admin.collection_products.flash.removed', ['product' => $product->name, 'name' => $collection->name]));
    }
}
