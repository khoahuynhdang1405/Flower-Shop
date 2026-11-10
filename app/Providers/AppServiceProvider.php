<?php

namespace App\Providers;

use App\Actions\Cart\CountCartItemsAction;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Số trên icon giỏ ở header storefront.
        View::composer('components.storefront.header', function (ViewInstance $view): void {
            $view->with('cartCount', $this->app->make(CountCartItemsAction::class)->execute());
        });
    }
}
