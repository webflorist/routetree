<?php

namespace RouteTreeTests\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __call($name, $arguments)
    {
        return json_encode([
            'id' => route_tree()->getCurrentNode()->getId(),
            'controller' => 'test',
            'function' => $name,
            'method' => \Request::getMethod(),
            'path' => \Request::getPathInfo(),
            'title' => route_tree()->getCurrentNode()->getTitle()
        ]);
    }

}
