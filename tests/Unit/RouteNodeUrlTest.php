<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;

class RouteNodeUrlTest extends TestCase
{

    public function testNodeUrlSimple()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            'http://localhost/de/page1',
            route_node_url('page1')
        );

    }

    public function testNodeUrlRelative()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            '/de/page1',
            route_node_url('page1', null, null, null, false)
        );

    }

}
