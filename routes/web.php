<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Cart\CartAddonController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CartFlowerController;
use App\Http\Controllers\Cart\CartPromotionController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FlowerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/flowers', [FlowerController::class, 'index'])->name('flowers.index');
Route::get('/flowers/{slug}', [FlowerController::class, 'show'])->name('flowers.show');

// TODO: chỉ giao diện với dữ liệu mẫu — khi nối DB thay bằng controller.
Route::view('/seasons', 'seasons.index')->name('seasons');

// Giỏ hàng (lưu trong session, khách chưa đăng nhập vẫn dùng được). {size} bỏ trống = sản phẩm chưa có size.
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('show');

    Route::controller(CartFlowerController::class)->prefix('flowers')->name('flowers.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::patch('/{product}/{size?}', 'update')->whereNumber(['product', 'size'])->name('update');
        Route::delete('/{product}/{size?}', 'destroy')->whereNumber(['product', 'size'])->name('destroy');
    });

    Route::controller(CartAddonController::class)->prefix('addons')->name('addons.')->group(function () {
        Route::patch('/{addon}', 'update')->whereNumber('addon')->name('update');
        Route::delete('/{addon}', 'destroy')->whereNumber('addon')->name('destroy');
    });

    Route::controller(CartPromotionController::class)->prefix('promotion')->name('promotion.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::delete('/', 'destroy')->name('destroy');
    });
});

// TODO: mới có giao diện — nút "Đặt hoa" chưa gửi đơn (chưa có route lưu orders / order_delivery_details).
Route::get('/checkout', CheckoutController::class)->name('checkout');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('account')->controller(ProfileController::class)->group(function () {
    Route::get('/', 'edit')->name('account');
    Route::patch('/profile', 'updateProfile')->name('account.profile.update');
    Route::patch('/address', 'updateAddress')->name('account.address.update');
    Route::put('/password', 'updatePassword')->middleware('throttle:6,1')->name('account.password.update');
});

Route::post('/locale/{locale}', LocaleController::class)
    ->whereIn('locale', array_keys(config('app.supported_locales')))
    ->name('locale.update');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');

        Route::get('/', DashboardController::class)->name('dashboard');

        // TODO: chỉ giao diện với dữ liệu mẫu — khi làm backend thay bằng ProductController.
        Route::view('/products', 'admin.products.index')->name('products');

        // Ảnh sản phẩm; {image} được giới hạn trong ảnh của {product} (scoped binding).
        Route::prefix('products/{product:slug}/images')->name('products.images')
            ->controller(ProductImageController::class)->scopeBindings()->group(function () {
                Route::get('/', 'edit');
                Route::post('/', 'store')->name('.store');
                Route::patch('/{image}', 'update')->name('.update');
                Route::post('/{image}/primary', 'primary')->name('.primary');
                Route::delete('/{image}', 'destroy')->name('.destroy');
            });

        // Tên route trang danh sách giữ "admin.collections" / "admin.promotions" cho menu (config/admin.php).
        Route::resource('collections', CategoryController::class)->except('show')->names(['index' => 'collections']);
        Route::resource('promotions', PromotionController::class)->except('show')->names(['index' => 'promotions']);
    });
});
