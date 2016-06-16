<?php

namespace RouteTreeTests;

class RouteTreeMethodsTest extends RouteTreeTestCase
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

    private function generateRoutes($visitUri='') {

        route_tree()->setRootNode([
            'namespace' => 'RouteTreeTests\Controllers',
            'index' => ['uses' => 'TestController@get'],
            'children' => [
                'page1' => [
                    'index' => ['uses' => 'TestController@get'],
                ]
            ]
        ]);

        // Visit the uri.
        try {
            $result = json_decode($this->visit($visitUri)->response->getContent(), true);
        }
        catch(NotFoundHttpException $exception) {
            throw $exception;
        }

    }




}
