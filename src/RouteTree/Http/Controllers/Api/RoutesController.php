<?php

namespace Webflorist\RouteTree\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Webflorist\RouteTree\Http\Resources\RouteCollection;

class RoutesController extends Controller
{

    public function index(Request $request)
    {
        return new RouteCollection(route_tree()->getRegisteredRoutes(true));
    }

}