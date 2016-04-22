<?php

use Nicat\RouteTree\RouteTree;

if (! function_exists('routeTree')) {
    /**
     * Get the available auth instance.
     *
     * @return \Nicat\RouteTree\RouteTree
     */
    function routeTree()
    {
        return app(RouteTree::class);
    }
}