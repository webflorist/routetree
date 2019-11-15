<?php

namespace Webflorist\RouteTree;

use Illuminate\Support\ServiceProvider;
use Webflorist\RouteTree\Middleware\RouteTreeMiddleware;
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
        $this->loadTranslations();
        $this->addMiddleware();
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

    private function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . "/../resources/lang", "Webflorist-RouteTree");
    }

    private function addMiddleware()
    {
        $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware(RouteTreeMiddleware::class);
    }
}