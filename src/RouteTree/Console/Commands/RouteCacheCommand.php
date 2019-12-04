<?php

namespace Webflorist\RouteTree\Console\Commands;

use Throwable;

class RouteCacheCommand extends \Illuminate\Foundation\Console\RouteCacheCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routetree:route-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Calls Laravel's route:cache and also caches the routetree";

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Throwable
     */
    public function handle()
    {
        parent::handle();

        route_tree()->cacheRouteTree();

        $this->info('RouteTree cached successfully!');
    }

    protected function getFreshApplication()
    {
        if (app()->environment() === 'testing') {
            return app();
        }

        return parent::getFreshApplication();
    }


}
