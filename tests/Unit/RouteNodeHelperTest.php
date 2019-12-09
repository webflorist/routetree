<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\Feature\Traits\UsesTestRoutes;
use RouteTreeTests\TestCase;

class RouteNodeHelperTest extends TestCase
{
    use UsesTestRoutes;

    public function test_route_node_simple()
    {
        $this->generateSimpleTestRoutes('/de/page1');

        $this->assertEquals(
            'page1',
            route_node('page1')->getId()
        );

    }

    public function test_route_node_with_fallback_to_root()
    {
        $this->generateSimpleTestRoutes('/de/page1');

        $this->assertEquals(
            '',
            route_node('i-do-not-exist')->getId()
        );

    }

}
