<?php
/**
 * Created by PhpStorm.
 * User: GeraldB
 * Date: 16.03.2016
 * Time: 11:51
 */

namespace Nicat\RouteTree;

use Illuminate\Support\ServiceProvider;

class RouteTreeServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Register the RouteTreeMiddleware.
        $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware(RouteTreeMiddleware::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        // Register the RouteTree singleton.
        $this->app->singleton(RouteTree::class, function()
        {
            return new RouteTree();
        });

    }

}