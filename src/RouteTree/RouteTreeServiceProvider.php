<?php

namespace Webflorist\RouteTree;

use Illuminate\Support\ServiceProvider;
use Webflorist\RouteTree\Middleware\SetLocalFromSession;

class RouteTreeServiceProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerService();
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->loadMigrations();
        $this->loadTranslations();
        $this->addMiddlewares();
    }

    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/routetree.php', 'routetree');
    }

    protected function registerService()
    {
        $this->app->singleton(RouteTree::class, function () {
            return new RouteTree();
        });
    }

    protected function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../config/routetree.php' => config_path('routetree.php'),
        ]);
    }


    private function loadMigrations()
    {
        if (config('routetree.database.enabled')) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    private function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . "/../resources/lang", "Webflorist-PackageBlueprint");
    }


    private function addMiddlewares()
    {
        $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware(RouteTreeMiddleware::class);
        if ($this->app['router']->hasMiddlewareGroup('web')) {
            $this->app['router']->pushMiddlewareToGroup('web', SetLocalFromSession::class);
        }
    }
}