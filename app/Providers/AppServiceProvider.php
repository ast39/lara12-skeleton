<?php

namespace App\Providers;

use App\Contracts\StorageServiceInterface;
use App\Services\S3Service;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StorageServiceInterface::class, S3Service::class);

        if ($this->app->environment('local')) {
            if (class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
                $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
                $this->app->register(\App\Providers\TelescopeServiceProvider::class);
            }
            if (class_exists(\Laravel\Horizon\HorizonServiceProvider::class)) {
                $this->app->register(\Laravel\Horizon\HorizonServiceProvider::class);
                $this->app->register(\App\Providers\HorizonServiceProvider::class);
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $router = $this->app->make(Router::class);

        $this->app->booted(function () {
            //
        });
    }
}
