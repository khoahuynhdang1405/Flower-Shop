<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\ProductImage\AssignProductImageSizeAction;
use App\Actions\ProductImage\DeleteProductImageAction;
use App\Actions\ProductImage\SetPrimaryProductImageAction;
use App\Actions\ProductImage\UploadProductImagesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductImagesRequest;
use App\Http\Requests\Admin\UpdateProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin › Sản phẩm › Ảnh: upload nhiều ảnh, gắn ảnh với size, chọn ảnh chính, xóa ảnh.
 * Route lồng {product:slug}/images/{image} nên ảnh luôn thuộc đúng sản phẩm trên URL.
 */
class ProductImageController extends Controller
{
    public function edit(Product $product, ProductRepositoryInterface $products): View
    {
        return view('admin.products.images', [
            'product' => $products->loadSizesAndImages($product),
            'maxFiles' => StoreProductImagesRequest::MAX_FILES,
            'maxMegabytes' => StoreProductImagesRequest::MAX_KILOBYTES / 1024,
        ]);
    }

    public function store(StoreProductImagesRequest $request, Product $product, UploadProductImagesAction $upload): RedirectResponse
    {
        $images = $upload->execute($request->toData());

        return $this->backToImages($product, trans_choice('admin.product_images.flash.uploaded', count($images)));
    }

    public function update(UpdateProductImageRequest $request, Product $product, ProductImage $image, AssignProductImageSizeAction $assignSize): RedirectResponse
    {
        $assignSize->execute($image, $request->productSizeId());

        return $this->backToImages($product, __('admin.product_images.flash.size_updated'));
    }

    public function primary(Product $product, ProductImage $image, SetPrimaryProductImageAction $setPrimary): RedirectResponse
    {
        $setPrimary->execute($image);

        return $this->backToImages($product, __('admin.product_images.flash.primary_updated'));
    }

    public function destroy(Product $product, ProductImage $image, DeleteProductImageAction $delete): RedirectResponse
    {
        $delete->execute($image);

        return $this->backToImages($product, __('admin.product_images.flash.deleted'));
    }

    private function backToImages(Product $product, string $status): RedirectResponse
    {
        return redirect()->route('admin.products.images', $product)->with('status', $status);
    }
}
