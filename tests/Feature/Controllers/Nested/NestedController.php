<?php

namespace RouteTreeTests\Feature\Controllers\Nested;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RouteTreeTests\TestCase;

class NestedController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __call($name, $arguments)
    {
        return TestCase::getRouteTestData([
            'controller' => 'nested',
            'function' => $name,
        ]);
    }

}
