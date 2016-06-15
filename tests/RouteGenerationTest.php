<?php

namespace RouteTreeTests;

use RouteTreeTests\Controllers\TestController;

class RouteGenerationTest extends RouteTreeTestCase
{

    protected $rootNode = [
        'namespace' => 'RouteTreeTests\Controllers',
        'index' => ['uses' => 'TestController@get']
    ];

    protected $nodeTree = [];

    protected $expectedResult = [
        "de.index" => [
            "method" => "GET",
            "uri" => "de",
            "action" => 'RouteTreeTests\Controllers\TestController@get',
            "middleware" => [],
            "content" => "controller:test|function:get|method:GET|path:/de",
        ],
        "en.index" => [
            "method" => "GET",
            "uri" => "en",
            "action" => 'RouteTreeTests\Controllers\TestController@get',
            "middleware" => [],
            "content" => "controller:test|function:get|method:GET|path:/en",
        ]
    ];

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

    public function testRootNodeView()
    {
        $this->rootNode = [
            'index' => ['view' => 'test']
        ];

        $this->expectedResult = [
            "de.index" => [
                "method" => "GET",
                "uri" => "de",
                "action" => "Closure",
                "middleware" => [],
                "content" => 'view:test|path:/de',
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => "Closure",
                "middleware" => [],
                "content" => 'view:test|path:/en',
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
                "content" => "controller:test|function:get|method:GET|path:/de",
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/en",
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
                "content" => "controller:test|function:get|method:GET|path:/de/target",
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => 'Closure',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/en/target",
            ],
            "de.target.index" => [
                "method" => "GET",
                "uri" => "de/target",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/de/target",
            ],
            "en.target.index" => [
                "method" => "GET",
                "uri" => "en/target",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/en/target",
            ]

        ];

        $this->performTest();
    }

    public function testNodeWithChildren()
    {

        $this->nodeTree = [
            'parent' => [
                'index' => ['uses' => 'TestController@get'],
                'children' => [
                    'child1' => [
                        'index' => ['uses' => 'TestController@get'],
                    ],
                    'child2' => [
                        'index' => ['uses' => 'TestController@get'],
                    ]
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.parent.index" => [
                "method" => "GET",
                "uri" => "de/parent",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/de/parent",
            ],
            "en.parent.index" => [
                "method" => "GET",
                "uri" => "en/parent",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/en/parent",
            ],
            "de.parent.child1.index" => [
                "method" => "GET",
                "uri" => "de/parent/child1",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/de/parent/child1",
            ],
            "en.parent.child1.index" => [
                "method" => "GET",
                "uri" => "en/parent/child1",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/en/parent/child1",
            ],
            "de.parent.child2.index" => [
                "method" => "GET",
                "uri" => "de/parent/child2",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/de/parent/child2",
            ],
            "en.parent.child2.index" => [
                "method" => "GET",
                "uri" => "en/parent/child2",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/en/parent/child2",
            ],

        ]);

        $this->performTest();
    }

    public function testCustomSegment()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@get'],
                'segment' => 'custom-segment'
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/custom-segment",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/de/custom-segment",
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/custom-segment",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/en/custom-segment",
            ]

        ]);

        $this->performTest();
    }

    public function testCustomSegmentPerLanguage()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@get'],
                'segment' => [
                    'de' => 'custom-segment-de',
                    'en' => 'custom-segment-en'
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/custom-segment-de",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/de/custom-segment-de",
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/custom-segment-en",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => "controller:test|function:get|method:GET|path:/en/custom-segment-en",
            ]

        ]);

        $this->performTest();
    }

    public function testActionCreate()
    {

        $this->nodeTree = [
            'page' => [
                'create' => ['uses' => 'TestController@create']
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.page.create" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => "controller:test|function:create|method:GET|path:/de/page",
            ],
            "en.page.create" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => "controller:test|function:create|method:GET|path:/en/page",
            ]

        ]);

        $this->performTest();
    }

    public function testActionsIndexAndStore()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@index'],
                'store' => ['uses' => 'TestController@store']
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/de/page",
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/en/page",
            ],
            "de.page.store" => [
                "method" => "POST",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [],
                "content" => "controller:test|function:store|method:POST|path:/de/page",
            ],
            "en.page.store" => [
                "method" => "POST",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [],
                "content" => "controller:test|function:store|method:POST|path:/en/page",
            ]

        ]);

        $this->performTest();
    }

    public function testActionsShowUpdateDestroy()
    {

        $this->nodeTree = [
            'page' => [
                'show' => ['uses' => 'TestController@show'],
                'update' => ['uses' => 'TestController@update'],
                'destroy' => ['uses' => 'TestController@destroy']
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.page.update" => [
                "method" => "PUT",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@update',
                "middleware" => [],
                "content" => "controller:test|function:update|method:PUT|path:/de/page",
            ],
            "en.page.update" => [
                "method" => "PUT",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@update',
                "middleware" => [],
                "content" => "controller:test|function:update|method:PUT|path:/en/page",
            ],
            "de.page.destroy" => [
                "method" => "DELETE",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => "controller:test|function:destroy|method:DELETE|path:/de/page",
            ],
            "en.page.destroy" => [
                "method" => "DELETE",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => "controller:test|function:destroy|method:DELETE|path:/en/page",
            ],
            "de.page.show" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@show',
                "middleware" => [],
                "content" => "controller:test|function:show|method:GET|path:/de/page",
            ],
            "en.page.show" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@show',
                "middleware" => [],
                "content" => "controller:test|function:show|method:GET|path:/en/page",
            ]

        ]);

        $this->performTest();
    }


    public function testActionEdit()
    {

        $this->nodeTree = [
            'page' => [
                'edit' => ['uses' => 'TestController@edit']
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.page.edit" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@edit',
                "middleware" => [],
                "content" => "controller:test|function:edit|method:GET|path:/de/page",
            ],
            "en.page.edit" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@edit',
                "middleware" => [],
                "content" => "controller:test|function:edit|method:GET|path:/en/page",
            ]

        ]);

        $this->performTest();
    }

    public function testResource()
    {

        $this->nodeTree = [
            'jobs' => [
                'resource' => [
                    'name' => 'job',
                    'controller' => 'TestController'
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/de/jobs",
            ],
            "en.jobs.index" => [
                "method" => "GET",
                "uri" => "en/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/en/jobs",
            ],
            "de.jobs.create" => [
                "method" => "GET",
                "uri" => "de/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => "controller:test|function:create|method:GET|path:/de/jobs/create",
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => "controller:test|function:create|method:GET|path:/en/jobs/create",
            ],
            "de.jobs.store" => [
                "method" => "POST",
                "uri" => "de/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [],
                "content" => "controller:test|function:store|method:POST|path:/de/jobs",
            ],
            "en.jobs.store" => [
                "method" => "POST",
                "uri" => "en/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [],
                "content" => "controller:test|function:store|method:POST|path:/en/jobs",
            ],
            "de.jobs.edit" => [
                "method" => "GET",
                "uri" => "de/jobs/{job}/edit",
                "action" => 'RouteTreeTests\Controllers\TestController@edit',
                "middleware" => [],
                "content" => "controller:test|function:edit|method:GET|path:/de/jobs/{job}/edit",
            ],
            "en.jobs.edit" => [
                "method" => "GET",
                "uri" => "en/jobs/{job}/edit",
                "action" => 'RouteTreeTests\Controllers\TestController@edit',
                "middleware" => [],
                "content" => "controller:test|function:edit|method:GET|path:/en/jobs/{job}/edit",
            ],
            "de.jobs.update" => [
                "method" => "PUT",
                "uri" => "de/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@update',
                "middleware" => [],
                "content" => "controller:test|function:update|method:PUT|path:/de/jobs/{job}",
            ],
            "en.jobs.update" => [
                "method" => "PUT",
                "uri" => "en/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@update',
                "middleware" => [],
                "content" => "controller:test|function:update|method:PUT|path:/en/jobs/{job}",
            ],
            "de.jobs.destroy" => [
                "method" => "DELETE",
                "uri" => "de/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => "controller:test|function:destroy|method:DELETE|path:/de/jobs/{job}",
            ],
            "en.jobs.destroy" => [
                "method" => "DELETE",
                "uri" => "en/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => "controller:test|function:destroy|method:DELETE|path:/en/jobs/{job}",
            ],
            "de.jobs.show" => [
                "method" => "GET",
                "uri" => "de/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@show',
                "middleware" => [],
                "content" => "controller:test|function:show|method:GET|path:/de/jobs/{job}",
            ],
            "en.jobs.show" => [
                "method" => "GET",
                "uri" => "en/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@show',
                "middleware" => [],
                "content" => "controller:test|function:show|method:GET|path:/en/jobs/{job}",
            ]

        ]);

        $this->performTest();
    }

    public function testResourceUsingOnly()
    {

        $this->nodeTree = [
            'jobs' => [
                'resource' => [
                    'name' => 'job',
                    'controller' => 'TestController',
                    'only' => ['index','create']
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/de/jobs",
            ],
            "en.jobs.index" => [
                "method" => "GET",
                "uri" => "en/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/en/jobs",
            ],
            "de.jobs.create" => [
                "method" => "GET",
                "uri" => "de/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => "controller:test|function:create|method:GET|path:/de/jobs/create",
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => "controller:test|function:create|method:GET|path:/en/jobs/create",
            ]

        ]);

        $this->performTest();
    }


    public function testResourceUsingExcept()
    {

        $this->nodeTree = [
            'jobs' => [
                'resource' => [
                    'name' => 'job',
                    'controller' => 'TestController',
                    'except' => ['show','update','destroy','edit','store']
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/de/jobs",
            ],
            "en.jobs.index" => [
                "method" => "GET",
                "uri" => "en/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/en/jobs",
            ],
            "de.jobs.create" => [
                "method" => "GET",
                "uri" => "de/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => "controller:test|function:create|method:GET|path:/de/jobs/create",
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => "controller:test|function:create|method:GET|path:/en/jobs/create",
            ]

        ]);

        $this->performTest();
    }

    public function testNoInheritPath()
    {

        $this->nodeTree = [
            'not_inherited' => [
                'index' => ['uses' => 'TestController@index'],
                'inheritPath' => false,
                'children' => [
                    'child1' => [
                        'index' => ['uses' => 'TestController@index'],
                    ],
                    'child2' => [
                        'index' => ['uses' => 'TestController@index'],
                    ]
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.not_inherited.index" => [
                "method" => "GET",
                "uri" => "de/not_inherited",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/de/not_inherited",
            ],
            "en.not_inherited.index" => [
                "method" => "GET",
                "uri" => "en/not_inherited",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/en/not_inherited",
            ],
            "de.not_inherited.child1.index" => [
                "method" => "GET",
                "uri" => "de/child1",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/de/child1",
            ],
            "en.not_inherited.child1.index" => [
                "method" => "GET",
                "uri" => "en/child1",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/en/child1",
            ],
            "de.not_inherited.child2.index" => [
                "method" => "GET",
                "uri" => "de/child2",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/de/child2",
            ],
            "en.not_inherited.child2.index" => [
                "method" => "GET",
                "uri" => "en/child2",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => "controller:test|function:index|method:GET|path:/en/child2",
            ],

        ]);

        $this->performTest();
    }

    public function testAppendNamespace()
    {

        $this->nodeTree = [
            'nested' => [
                'index' => ['uses' => 'NestedController@index'],
                'appendNamespace' => 'Nested'
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult,[
            "de.nested.index" => [
                "method" => "GET",
                "uri" => "de/nested",
                "action" => 'RouteTreeTests\Controllers\Nested\NestedController@index',
                "middleware" => [],
                "content" => "controller:nested|function:index|method:GET|path:/de/nested",
            ],
            "en.nested.index" => [
                "method" => "GET",
                "uri" => "en/nested",
                "action" => 'RouteTreeTests\Controllers\Nested\NestedController@index',
                "middleware" => [],
                "content" => "controller:nested|function:index|method:GET|path:/en/nested",
            ]

        ]);

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
            $method = str_replace('|HEAD','',implode('|', $route->methods()));
            $uri = $route->uri();
            $routes[$route->getName()] = [
                'method' => $method,
                'uri'    => $uri,
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
                'content' => $this->makeRequest($method,$uri)->response->getContent()
            ];
        }

        // Sort expected and actual routes-array by key
        ksort($routes);
        ksort($this->expectedResult);

        // Assert, that expected and actual routes-array are qequal.
        $this->assertEquals(
            $routes,
            $this->expectedResult
        );
    }

}
