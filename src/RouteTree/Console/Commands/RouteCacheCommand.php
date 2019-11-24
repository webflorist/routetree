<?php

namespace Webflorist\RouteTree\Console\Commands;

use Illuminate\Console\Command;
use Throwable;
use Webflorist\RouteTree\Domain\RegisteredRoute;

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
    protected $description = "Calls Laravel's route:cache and also cached routetree";

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Throwable
     */
    public function handle()
    {
        parent::handle();
    }

    protected function getFreshApplication()
    {
        if (app()->environment() === 'testing') {
            return app();
        }

        return parent::getFreshApplication();
    }


}
