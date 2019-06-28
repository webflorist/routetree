<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;

class MethodsTest extends TestCase
{

    protected $result = [];

    public function testGetCurrentNode()
    {
        $this->generateRoutes('/de/page1');

        $this->assertEquals(
            'page1',
            route_tree()->getCurrentNode()->getId()
        );

    }

    public function testGetCurrentAction()
    {
        $this->generateRoutes('/de/page1');

        $this->assertEquals(
            'page1.index',
            route_tree()->getCurrentAction()->getRouteNode()->getId().'.'.route_tree()->getCurrentAction()->getAction()
        );

    }

    public function testDoesNodeExist()
    {
        $this->generateRoutes('/de/page1');

        $this->assertEquals(
            true,
            route_tree()->doesNodeExist('page1.page1-1')
        );

    }

    public function testDoesNodeNotExist()
    {
        $this->generateRoutes('/de/page1');

        $this->assertEquals(
            false,
            route_tree()->doesNodeExist('page1.i-do-not-exist')
        );

    }

    public function testGetNodeByRouteName()
    {
        $this->generateRoutes('/de/page1');

        $this->assertEquals(
            'page1.page1-1',
            route_tree()->getNodeByRouteName('de.page1.page1-1.index')->getId()
        );

    }

    public function testGetActionByRoute()
    {
        $this->generateRoutes('/de/page1');

        $this->assertEquals(
            'index',
            route_tree()->getActionByRoute(\Route::current())->getAction()
        );

    }

    private function generateRoutes($visitUri='') {

        route_tree()->setRootNode([
            'namespace' => 'RouteTreeTests\Feature\Controllers',
            'index' => ['uses' => 'TestController@get'],
            'children' => [
                'page1' => [
                    'index' => ['uses' => 'TestController@get'],
                    'children' => [
                        'page1-1' => [
                            'index' => ['uses' => 'TestController@get'],
                        ]
                    ]
                ]
            ]
        ]);

        // Visit the uri.
        try {
            $result = json_decode($this->get($visitUri)->baseResponse->getContent(), true);
        }
        catch(NotFoundHttpException $exception) {
            throw $exception;
        }

    }




}
