<?php

namespace App\Providers;

use App\Support\DbPublicDisk;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem as Flysystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Vercel has no writable disk, so FILESYSTEM_PUBLIC_DRIVER=db swaps
        // the public disk for media-table storage (see DbPublicDisk). Local
        // dev keeps the default "local" driver and never enters this path.
        Storage::extend('db', function ($app, $config) {
            $adapter = new DbPublicDisk;

            return new FilesystemAdapter(new Flysystem($adapter), $adapter, $config);
        });
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
