<?php

namespace Larangogon\ThreeDS\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->loadConfig();
        $this->loadMigrations();
        $this->loadViews();
    }

    private function loadConfig(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/something.php' => config_path('something.php'),
            ],
            'package-name-config'
        );
    }

    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes(
            [
                __DIR__ . '/../database/migrations' => base_path('database/migrations'),
            ],
            'package-name-migrations'
        );
    }

    private function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'package-name-view');

        $this->publishes(
            [
                __DIR__ . '/../resources/views' => resource_path('views/vendor/package-name'),
            ],
            'package-name-views'
        );
    }
}