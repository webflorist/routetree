<?php

namespace RouteTreeTests\Feature\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;
use Throwable;
use Webflorist\RouteTree\Domain\RegisteredRoute;

class RouteCacheCommand extends \Illuminate\Foundation\Console\RouteCacheCommand
{

    protected function getFreshApplication()
    {

        dd(112211);
        if (app()->environment() === 'testing') {
            return app();
        }

        return parent::getFreshApplication();
    }


}
