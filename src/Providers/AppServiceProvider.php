<?php

namespace Rashiqulrony\LaravelImageUpload\Providers;

use Illuminate\Support\ServiceProvider;
use Rashiqulrony\LaravelImageUpload\Uploader;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/imageupload.php', 'imageupload');

        $this->app->bind('imageupload', function () {
            return new Uploader();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/imageupload.php' => config_path('imageupload.php'),
        ], 'config');
    }
}
