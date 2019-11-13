<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\LegacyTestCase;

class RouteNodeUrlLegacyTest extends LegacyTestCase
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

    public function testNodeUrlRelativeViaConfig()
    {
        config()->set('routetree.absolute_urls', false);
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            '/de/page1',
            route_node_url('page1')
        );

    }

}
