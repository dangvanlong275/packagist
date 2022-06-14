<?php

namespace StdioTemplate\FileHelper;

use Illuminate\Support\ServiceProvider;

class StdioServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->publishes([
            __DIR__ . '/publishs/Models/StorageFile.php' => base_path('app/Models/StorageFile.php'),
            __DIR__ . '/publishs/config/ffmpeg.php' => base_path('config/ffmpeg.php')
        ], 'stdio-template-helper');

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
