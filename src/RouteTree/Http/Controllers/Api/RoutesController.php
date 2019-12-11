<?php

namespace Webflorist\RouteTree\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webflorist\RouteTree\Http\Resources\Route;
use Webflorist\RouteTree\Http\Resources\RouteCollection;
use Webflorist\RouteTree\RegisteredRoute;

class RoutesController extends Controller
{

    public function index(Request $request)
    {
        return new RouteCollection(route_tree()->getRegisteredRoutes(true));
    }

    public function show(string $routeId, Request $request)
    {
        $foundRegisteredRoute = null;
        route_tree()->getRegisteredRoutes(true)->each(function (RegisteredRoute $registeredRoute) use($routeId, &$foundRegisteredRoute) {
            if ($registeredRoute->routeName === $routeId) {
                $foundRegisteredRoute = $registeredRoute;
            }
        });
        if ($foundRegisteredRoute !== null) {
            return new Route($foundRegisteredRoute);
        }
        return response(null,404);
    }

}