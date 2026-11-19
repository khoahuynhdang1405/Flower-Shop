<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CollectionProductController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DashboardRevenueController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
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
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SeasonController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/flowers', [FlowerController::class, 'index'])->name('flowers.index');
Route::get('/flowers/{slug}', [FlowerController::class, 'show'])->name('flowers.show');

// TODO: sản phẩm của mùa vẫn là dữ liệu mẫu trong view — khi nối DB lấy theo bộ sưu tập.
Route::get('/seasons', SeasonController::class)->name('seasons');

Route::get('/journal', [PostController::class, 'index'])->name('posts.index');
Route::get('/journal/{slug}', [PostController::class, 'show'])->name('posts.show');

// Khối nhận tin cuối Trang chủ chỉ dành cho khách chưa đăng nhập.
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware(['guest', 'throttle:5,1'])->name('newsletter.store');

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

// Đặt hoa cần đăng nhập: khách vãng lai được đưa tới /login, đăng nhập xong quay lại trang thanh toán (intended).
Route::middleware('auth')->group(function () {
    Route::get('/checkout', CheckoutController::class)->name('checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');

    // Tra cứu đơn: chỉ tìm trong đơn của tài khoản đang đăng nhập (TrackOrderAction).
    Route::get('/orders/track', [OrderController::class, 'track'])->middleware('throttle:30,1')->name('orders.track');

    // Trang xác nhận đơn và thanh toán lại: chỉ trình duyệt đã đặt đơn mới mở được (PlacedOrderRequest).
    Route::prefix('orders/{order}')->whereNumber('order')->name('orders.')->controller(OrderController::class)->group(function () {
        Route::get('/', 'show')->name('confirmation');
        Route::post('/payment', 'pay')->middleware('throttle:10,1')->name('payment');
    });
});

// Cổng thanh toán gửi kết quả về: trình duyệt khách quay lại (return) và cổng gọi ngầm (IPN, không có CSRF).
Route::prefix('payment/{method}')->whereIn('method', ['vnpay', 'momo'])->name('payment.')->controller(PaymentController::class)->group(function () {
    Route::get('/return', 'return')->name('return');
    Route::match(['get', 'post'], '/ipn', 'ipn')->withoutMiddleware(ValidateCsrfToken::class)->name('ipn');
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
        Route::get('/dashboard/revenue', DashboardRevenueController::class)->middleware('throttle:60,1')->name('dashboard.revenue');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders');
        Route::get('/orders/schedule', [AdminOrderController::class, 'schedule'])->name('orders.schedule');
        Route::post('/orders/{order}/advance', [AdminOrderController::class, 'advance'])->name('orders.advance');

        // Tên route danh sách giữ "admin.posts" cho menu (config/admin.php).
        Route::post('/posts/preview', [AdminPostController::class, 'preview'])->name('posts.preview');
        Route::post('/posts/images', [AdminPostController::class, 'uploadImage'])->middleware('throttle:30,1')->name('posts.images');
        Route::resource('posts', AdminPostController::class)->except('show')->names(['index' => 'posts']);
        // Tên route danh sách giữ "admin.customers" cho menu (config/admin.php).
        Route::controller(CustomerController::class)->prefix('customers')->name('customers')->group(function () {
            Route::get('/', 'index');
            Route::get('/{customer}', 'show')->whereNumber('customer')->name('.show');
            Route::put('/{customer}', 'update')->whereNumber('customer')->name('.update');
        });

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

        // Sản phẩm của bộ sưu tập: Theo mùa / Theo dịp thêm/bỏ qua pivot category_product, Kiểu hoa chỉ xem.
        Route::prefix('collections/{collection}/products')->name('collections.products')
            ->controller(CollectionProductController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store')->name('.store');
                Route::delete('/{product}', 'destroy')->whereNumber('product')->name('.destroy');
            });
        Route::resource('promotions', PromotionController::class)->except('show')->names(['index' => 'promotions']);
    });
});
