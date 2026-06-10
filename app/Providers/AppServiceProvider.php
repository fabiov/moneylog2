<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Storage::extend('dropbox', function ($app, $config) {
            $client = new DropboxClient([
                $config['app_key'],
                $config['app_secret'],
                $config['refresh_token'],
            ]);

            $adapter = new DropboxAdapter($client);

            $filesystem = new Filesystem($adapter, ['public_url' => $config['url'] ?? null]);

            return new FilesystemAdapter(
                $filesystem,
                $adapter,
                $config
            );
        });
    }
}
