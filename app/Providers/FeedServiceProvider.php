<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\FeedService;

class FeedServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(FeedService::class, function ($app) {
            return new FeedService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

