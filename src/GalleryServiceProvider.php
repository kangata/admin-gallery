<?php

namespace QuetzalArc\Admin\Gallery;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class GalleryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadViewsFrom(__DIR__.'/views', 'admin-gallery');

        $this->publishes([
            __DIR__.'/public' => public_path('vendor/quetzalarc')
        ], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function map()
    {
        Route::group(['middleware' => ['web', 'auth']], function ($router) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        });
    }
}