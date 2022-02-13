<?php

namespace Larangogon\ThreeDS\Providers;

use Illuminate\Support\ServiceProvider;
use Larangogon\ThreeDS\Contracts\ClientContract;
use Larangogon\ThreeDS\Services\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'threeds');

        $this->app->bind(ClientContract::class, Client::class);
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
                __DIR__ . '/../../config' => config_path('vendor/threeds'),
            ],
            'threeds-config'
        );
    }

    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->publishes(
            [
                __DIR__ . '/../../database/migrations' => base_path('database/migrations'),
            ],
            'threeds-migrations'
        );
    }

    private function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', '/view');

        $this->publishes(
            [
                __DIR__ . '/../../resources/views' => resource_path('views/vendor/threeds'),
            ],
            'threeds-views'
        );
    }
}
