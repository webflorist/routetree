<?php

namespace Webflorist\RouteTree;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Webflorist\RouteTree\Console\Commands\GenerateSitemapCommand;
use Webflorist\RouteTree\Console\Commands\RouteCacheCommand;
use Webflorist\RouteTree\Console\Commands\RouteClearCommand;
use Webflorist\RouteTree\Http\Controllers\Api\RoutesController;
use Webflorist\RouteTree\Http\Middleware\RouteTreeMiddleware;
use Webflorist\RouteTree\Http\Middleware\SessionLocaleMiddleware;

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
        $this->registerArtisanCommands();
        $this->loadTranslations();
        $this->addMiddleware();
        $this->loadViews();
        $this->addRoutes();

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

    protected function registerArtisanCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSitemapCommand::class,
                RouteCacheCommand::class,
                RouteClearCommand::class
            ]);
        }
    }

    private function loadTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . "/../resources/lang", "Webflorist-RouteTree");
    }

    private function loadViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'webflorist-routetree');
    }

    private function addMiddleware()
    {
        /** @var Kernel $kernel */
        $kernel = $this->app['Illuminate\Contracts\Http\Kernel'];
        $kernel->pushMiddleware(RouteTreeMiddleware::class);


        /** @var Router $router */
        $router = $this->app['router'];
        if($router->hasMiddlewareGroup('web')) {
            $router->pushMiddlewareToGroup('web', SessionLocaleMiddleware::class);
        }
    }

    private function addRoutes()
    {
        /** @var Router $router */
        $router = $this->app['router'];
        if (config('routetree.api.enabled')) {
            $router->group(['prefix' => config('routetree.api.base_path'), 'middleware' => 'api'], function (Router $router) {
                $router->resource('routes', RoutesController::class)->only(['index', 'show']);
            });
        }
    }
}