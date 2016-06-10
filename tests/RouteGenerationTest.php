<?php

namespace RouteTreeTests;

use RouteTreeTests\Controllers\TestController;

class RouteGenerationTest extends RouteTreeTestCase
{

    protected $rootNode = [];

    protected $nodeTree = [];

    protected $expectedResult = [];

    public function testRootNodeClosure()
    {
        $this->rootNode = [
            'index' => ['closure' => function () {
                return 'home';
            }]
        ];

        $this->expectedResult = [
            "de.index" => [
                "method" => "GET",
                "uri" => "de",
                "action" => "Closure",
                "middleware" => [],
                "content" => "home",
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => "Closure",
                "middleware" => [],
                "content" => "home",
            ]

        ];

        $this->performTest();
    }

    public function testRootNodeController()
    {
        $this->rootNode = [
            'namespace' => 'RouteTreeTests\Controllers',
            'index' => ['uses' => 'TestController@get']
        ];

        $this->expectedResult = [
            "de.index" => [
                "method" => "GET",
                "uri" => "de",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "path:/de",
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "path:/en",
            ]

        ];

        $this->performTest();
    }

    public function testRootNodeRedirect()
    {
        $this->rootNode = [
            'namespace' => 'RouteTreeTests\Controllers',
            'index' => ['redirect' => 'target']
        ];

        $this->nodeTree = [
            'target' => [
                'index' => ['uses' => 'TestController@get']
            ]
        ];

        $this->expectedResult = [
            "de.index" => [
                "method" => "GET",
                "uri" => "de",
                "action" => 'Closure',
                "middleware" => [],
                "content" => "path:/de/target",
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => 'Closure',
                "middleware" => [],
                "content" => "path:/en/target",
            ],
            "de.target.index" => [
                "method" => "GET",
                "uri" => "de/target",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "path:/de/target",
            ],
            "en.target.index" => [
                "method" => "GET",
                "uri" => "en/target",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "path:/en/target",
            ]

        ];

        $this->performTest();
    }


    protected function performTest() {

        // Set root-node
        route_tree()->setRootNode($this->rootNode);

        // Set nodes
        route_tree()->addNodes($this->nodeTree);

        // Visit the root
        $this->visit('');

        // Accumulate all routes
        $routes = [];
        foreach (\Route::getRoutes() as $route) {
            $routes[$route->getName()] = [
                'method' => str_replace('|HEAD','',implode('|', $route->methods())),
                'uri'    => $route->uri(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
                'content' => $this->visit($route->uri())->response->getContent()
            ];
        }

        ksort($routes);
        ksort($this->expectedResult);

        $this->assertEquals(
            $routes,
            $this->expectedResult
        );
    }

}
