<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Collection\CreateCollectionAction;
use App\Actions\Collection\DeleteCollectionAction;
use App\Actions\Collection\UpdateCollectionAction;
use App\Enums\CategoryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Admin › Bộ sưu tập: thêm / sửa / xóa bản ghi bảng categories.
 */
class CategoryController extends Controller
{
    /**
     * Danh sách đầy đủ (tiệm chỉ có vài chục bộ sưu tập); tìm kiếm / lọc chạy phía trình duyệt
     * (resources/js/list-filter.js).
     */
    public function index(): View
    {
        $categories = Category::query()->withProductsCount()->latest('id')->get();

        return view('admin.collections.index', [
            'categories' => $categories,
            'stats' => [
                'total' => $categories->count(),
                'published' => $categories->where('is_active', true)->count(),
                'hidden' => $categories->where('is_active', false)->count(),
                'season' => $categories->where('type', CategoryType::Season)->count(),
                'occasion' => $categories->where('type', CategoryType::Occasion)->count(),
                'featured' => DB::table('products')->where('is_featured', true)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.collections.create', ['category' => new Category]);
    }

    public function store(StoreCategoryRequest $request, CreateCollectionAction $createCollection): RedirectResponse
    {
        $category = $createCollection->execute($request->toData());

        return redirect()->route('admin.collections')
            ->with('status', __('admin.collections.flash.created', ['name' => $category->name]));
    }

    public function edit(Category $collection): View
    {
        return view('admin.collections.edit', ['category' => $collection]);
    }

    public function update(UpdateCategoryRequest $request, Category $collection, UpdateCollectionAction $updateCollection): RedirectResponse
    {
        $updateCollection->execute($collection, $request->toData());

        return redirect()->route('admin.collections')
            ->with('status', __('admin.collections.flash.updated', ['name' => $collection->name]));
    }

    /**
     * Bộ sưu tập còn sản phẩm (kiểu hoa chính) thì không xóa, gợi ý ẩn đi (DeleteCollectionAction).
     */
    public function destroy(Category $collection, DeleteCollectionAction $deleteCollection): RedirectResponse
    {
        if (! $deleteCollection->execute($collection)) {
            return back()->with('error', __('admin.collections.flash.has_products', ['name' => $collection->name]));
        }

        return redirect()->route('admin.collections')
            ->with('status', __('admin.collections.flash.deleted', ['name' => $collection->name]));
    }
}
