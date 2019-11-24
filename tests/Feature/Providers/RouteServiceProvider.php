<?php

namespace RouteTreeTests\Feature\Providers;

use RouteTreeTests\Feature\Traits\UsesTestRoutes;

class RouteServiceProvider extends \Illuminate\Foundation\Support\Providers\RouteServiceProvider
{
    use UsesTestRoutes;

    protected function loadRoutes()
    {
        self::generateComplexTestRoutes(route_tree());
    }


}