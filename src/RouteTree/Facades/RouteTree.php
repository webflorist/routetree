<?php

namespace Webflorist\RouteTree\Facades;

use Illuminate\Support\Facades\Facade;
use Webflorist\RouteTree\RouteTree as RouteTreeService;

/**
 * @see \Webflorist\RouteTree\RouteTree
 */
class RouteTree extends Facade
{

    /**
     * Static access-proxy for the PackageBlueprint
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return RouteTreeService::class;
    }

}