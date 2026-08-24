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
        // Satisfies the cahier des charges' HTTPS-in-production requirement
        // (section 10) without affecting local dev, which runs over HTTP.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
