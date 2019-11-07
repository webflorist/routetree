<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;

class MethodsTest extends TestCase
{

    public function testGetCurrentNode()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            'page1',
            route_tree()->getCurrentNode()->getId()
        );

    }

    public function testGetCurrentAction()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            'page1.get',
            route_tree()->getCurrentAction()->getRouteNode()->getId().'.'.route_tree()->getCurrentAction()->getName()
        );

    }

    public function testDoesNodeExist()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            true,
            route_tree()->doesNodeExist('page1.page1-1')
        );

    }

    public function testDoesNodeNotExist()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            false,
            route_tree()->doesNodeExist('page1.i-do-not-exist')
        );

    }

    public function testGetNodeByRouteName()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            'page1.page1-1',
            route_tree()->getNodeByRouteName('de.page1.page1-1.get')->getId()
        );

    }

    public function testGetActionByRoute()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            'get',
            route_tree()->getActionByRoute(\Route::current())->getName()
        );

    }




}
