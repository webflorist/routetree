<?php

namespace RouteTreeTests\Feature\Console\Commands;

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
