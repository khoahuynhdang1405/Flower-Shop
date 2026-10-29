<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

        // Tên route trang danh sách giữ "admin.collections" / "admin.promotions" cho menu (config/admin.php).
        Route::resource('collections', CategoryController::class)->except('show')->names(['index' => 'collections']);
        Route::resource('promotions', PromotionController::class)->except('show')->names(['index' => 'promotions']);
    });
});
