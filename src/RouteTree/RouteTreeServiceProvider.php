<?php

namespace Webflorist\RouteTree;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Webflorist\RouteTree\Http\Controllers\Api\RoutesController;
use Webflorist\RouteTree\Http\Middleware\RouteTreeMiddleware;
use Webflorist\RouteTree\Http\Middleware\SetLocalFromSession;
use Webflorist\RouteTree\Console\Commands\GenerateSitemapCommand;

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
	    $this->addGlobalMiddleware(RouteTreeMiddleware::class);
        $this->loadViews();
        $this->addRoutes($this->app['router']);
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
                GenerateSitemapCommand::class
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

    private function addGlobalMiddleware(string $middleware)
    {
        $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware($middleware);
    }

    private function addRoutes( Router $router)
    {
        if (config('routetree.api.enabled')) {
            $router->group(['prefix' => config('routetree.api.base_path'), 'middleware' => 'api'], function (Router $router) {
                $router->resource('paths', RoutesController::class)->only('index');
            });
        }
    }
}