<?php

use App\DTOs\Cart\CartItemKeyData;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\CartPromotionController;
use Illuminate\Support\Facades\Route;

/*
 * API JSON (prefix /api, tên route api.*). Nạp trong bootstrap/app.php với middleware "web":
 * giỏ hàng lưu trong session nên cần cookie phiên + CSRF (header X-XSRF-TOKEN) như form web.
 */

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');

    Route::controller(CartItemController::class)->prefix('items')->name('items.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::patch('/{item}', 'update')->where('item', CartItemKeyData::PATTERN)->name('update');
        Route::delete('/{item}', 'destroy')->where('item', CartItemKeyData::PATTERN)->name('destroy');
    });

    Route::controller(CartPromotionController::class)->prefix('promotions')->name('promotions.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::delete('/', 'destroy')->name('destroy');
    });
});
