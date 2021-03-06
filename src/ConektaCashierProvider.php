<?php

namespace UvealSnow\ConektaCashier;

use Illuminate\Support\ServiceProvider;

class ConektaCashierProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerMigrations();
    }

    /**
     * Register Conecka Cashier's migration files.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        return $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register Conecka Cashier's migration files.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        return $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('UvealSnow\ConektaCashier\Controllers\WebhookController');
    }
}
