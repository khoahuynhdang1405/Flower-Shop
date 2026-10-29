<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin › Ưu đãi: thêm / sửa / xóa bản ghi bảng promotions.
 */
class PromotionController extends Controller
{
    /**
     * Danh sách đầy đủ; tìm kiếm / lọc chạy phía trình duyệt (resources/js/list-filter.js).
     */
    public function index(): View
    {
        $promotions = Promotion::query()->withOrdersCount()->orderByDesc('is_active')->orderBy('code')->get();
        $active = $promotions->where('is_active', true);

        return view('admin.promotions.index', [
            'promotions' => $promotions,
            'stats' => [
                'total' => $promotions->count(),
                'active' => $active->count(),
                'paused' => $promotions->count() - $active->count(),
                'usage' => (int) $active->sum('orders_count'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.create', ['promotion' => new Promotion]);
    }

    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $promotion = Promotion::create($request->validated());

        return redirect()->route('admin.promotions')
            ->with('status', __('admin.promotions.flash.created', ['code' => $promotion->code]));
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.edit', ['promotion' => $promotion]);
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($request->validated());

        return redirect()->route('admin.promotions')
            ->with('status', __('admin.promotions.flash.updated', ['code' => $promotion->code]));
    }

    /**
     * Mã đã có đơn dùng thì không xóa (orders.promotion_id sẽ bị set null, mất dấu ưu đãi trên đơn cũ).
     * Gợi ý tạm dừng mã thay vì xóa.
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        if ($promotion->hasOrders()) {
            return back()->with('error', __('admin.promotions.flash.has_orders', ['code' => $promotion->code]));
        }

        $promotion->delete();

        return redirect()->route('admin.promotions')
            ->with('status', __('admin.promotions.flash.deleted', ['code' => $promotion->code]));
    }
}
