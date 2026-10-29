<?php

namespace App\Http\Controllers\Admin;

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

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()->route('admin.collections')
            ->with('status', __('admin.collections.flash.created', ['name' => $category->name]));
    }

    public function edit(Category $collection): View
    {
        return view('admin.collections.edit', ['category' => $collection]);
    }

    public function update(UpdateCategoryRequest $request, Category $collection): RedirectResponse
    {
        $collection->update($request->validated());

        return redirect()->route('admin.collections')
            ->with('status', __('admin.collections.flash.updated', ['name' => $collection->name]));
    }

    /**
     * Không xóa bộ sưu tập còn sản phẩm: khóa ngoại products.category_id đang để cascade,
     * xóa sẽ kéo theo toàn bộ sản phẩm. Gợi ý ẩn bộ sưu tập thay vì xóa.
     */
    public function destroy(Category $collection): RedirectResponse
    {
        if ($collection->hasProducts()) {
            return back()->with('error', __('admin.collections.flash.has_products', ['name' => $collection->name]));
        }

        $collection->delete();

        return redirect()->route('admin.collections')
            ->with('status', __('admin.collections.flash.deleted', ['name' => $collection->name]));
    }
}
