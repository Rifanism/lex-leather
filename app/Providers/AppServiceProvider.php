<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Render terminates TLS in front of the container; without this the
        // asset/url helpers would emit http:// and break on mixed content.
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
