<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/locale/{locale}', LocaleController::class)
    ->whereIn('locale', array_keys(config('app.supported_locales')))
    ->name('locale.update');

// TODO: thêm middleware auth:admin khi đã có guard admin.
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
});
