<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\PengaturanWebsite;
use App\Models\Buku;
use App\Observers\BukuObserver;

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
        // Force HTTPS in production
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Register Buku Observer
        Buku::observe(BukuObserver::class);

        // Share pengaturan website ke semua view
        View::composer('*', function ($view) {
            $pengaturan = PengaturanWebsite::first();
            $view->with('pengaturan', $pengaturan);
        });
    }
}
