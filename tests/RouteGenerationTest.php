<?php

namespace RouteTreeTests;

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
            "content" => [
                'id' => '',
                'controller' => 'test',
                'function' => 'get',
                'method' => 'GET',
                'path' => 'de',
                'title' => 'Startseite'
            ],
        ],
        "en.index" => [
            "method" => "GET",
            "uri" => "en",
            "action" => 'RouteTreeTests\Controllers\TestController@get',
            "middleware" => [],
            "content" => [
                'id' => '',
                'controller' => 'test',
                'function' => 'get',
                'method' => 'GET',
                'path' => 'en',
                'title' => 'Startpage'
            ],
        ]
    ];

    public function testRootNodeController()
    {
        $this->performFullRoutesTest();
    }

    public function testRootNodeClosure()
    {
        $this->rootNode = [
            'index' => ['closure' => function () {
                return json_encode([
                    'id' => route_tree()->getCurrentNode()->getId(),
                    'method' => \Request::getMethod(),
                    'path' => trim(\Request::getPathInfo(),'/'),
                    'title' => route_tree()->getCurrentNode()->getTitle()
                ]);
            }]
        ];

        $this->expectedResult = [
            "de.index" => [
                "method" => "GET",
                "uri" => "de",
                "action" => "Closure",
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'method' => 'GET',
                    'path' => 'de',
                    'title' => 'Startseite'
                ],
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => "Closure",
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'method' => 'GET',
                    'path' => 'en',
                    'title' => 'Startpage'
                ],
            ]

        ];

        $this->performFullRoutesTest();
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
                "content" => [
                    'id' => '',
                    'view' => 'test',
                    'method' => 'GET',
                    'path' => 'de',
                    'title' => 'Startseite'
                ],
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => "Closure",
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'view' => 'test',
                    'method' => 'GET',
                    'path' => 'en',
                    'title' => 'Startpage'
                ],
            ]

        ];

        $this->performFullRoutesTest();
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
                "content" => [
                    'id' => 'target',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/target',
                    'title' => 'Target'
                ],
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => 'Closure',
                "middleware" => [],
                "content" => [
                    'id' => 'target',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/target',
                    'title' => 'Target'
                ],
            ],
            "de.target.index" => [
                "method" => "GET",
                "uri" => "de/target",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'target',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/target',
                    'title' => 'Target'
                ],
            ],
            "en.target.index" => [
                "method" => "GET",
                "uri" => "en/target",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'target',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/target',
                    'title' => 'Target'
                ],
            ]

        ];

        $this->performFullRoutesTest();
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

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.parent.index" => [
                "method" => "GET",
                "uri" => "de/parent",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/parent',
                    'title' => 'Parent'
                ],
            ],
            "en.parent.index" => [
                "method" => "GET",
                "uri" => "en/parent",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/parent',
                    'title' => 'Parent'
                ],
            ],
            "de.parent.child1.index" => [
                "method" => "GET",
                "uri" => "de/parent/child1",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/parent/child1',
                    'title' => 'Child1'
                ],
            ],
            "en.parent.child1.index" => [
                "method" => "GET",
                "uri" => "en/parent/child1",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/parent/child1',
                    'title' => 'Child1'
                ],
            ],
            "de.parent.child2.index" => [
                "method" => "GET",
                "uri" => "de/parent/child2",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/parent/child2',
                    'title' => 'Child2'
                ],
            ],
            "en.parent.child2.index" => [
                "method" => "GET",
                "uri" => "en/parent/child2",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/parent/child2',
                    'title' => 'Child2'
                ],
            ],

        ]);

        $this->performFullRoutesTest();
    }

    public function testCustomSegment()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@get'],
                'segment' => 'custom-segment'
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/custom-segment",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/custom-segment',
                    'title' => 'Page'
                ],
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/custom-segment",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/custom-segment',
                    'title' => 'Page'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
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

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/custom-segment-de",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/custom-segment-de',
                    'title' => 'Page'
                ],
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/custom-segment-en",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/custom-segment-en',
                    'title' => 'Page'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
    }

    public function testActionCreate()
    {

        $this->nodeTree = [
            'page' => [
                'create' => ['uses' => 'TestController@create']
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.create" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.create" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
    }

    public function testActionsIndexAndStore()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@index'],
                'store' => ['uses' => 'TestController@store']
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ],
            "de.page.store" => [
                "method" => "POST",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'store',
                    'method' => 'POST',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.store" => [
                "method" => "POST",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'store',
                    'method' => 'POST',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
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

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.update" => [
                "method" => "PUT",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@update',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'update',
                    'method' => 'PUT',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.update" => [
                "method" => "PUT",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@update',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'update',
                    'method' => 'PUT',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ],
            "de.page.destroy" => [
                "method" => "DELETE",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'destroy',
                    'method' => 'DELETE',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.destroy" => [
                "method" => "DELETE",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'destroy',
                    'method' => 'DELETE',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ],
            "de.page.show" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@show',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'show',
                    'method' => 'GET',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.show" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@show',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'show',
                    'method' => 'GET',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
    }


    public function testActionEdit()
    {

        $this->nodeTree = [
            'page' => [
                'edit' => ['uses' => 'TestController@edit']
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.edit" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@edit',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'edit',
                    'method' => 'GET',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.edit" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@edit',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'edit',
                    'method' => 'GET',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
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

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/jobs',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.index" => [
                "method" => "GET",
                "uri" => "en/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/jobs',
                    'title' => 'Jobs'
                ],
            ],
            "de.jobs.create" => [
                "method" => "GET",
                "uri" => "de/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'de/jobs/create',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'en/jobs/create',
                    'title' => 'Jobs'
                ],
            ],
            "de.jobs.store" => [
                "method" => "POST",
                "uri" => "de/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'store',
                    'method' => 'POST',
                    'path' => 'de/jobs',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.store" => [
                "method" => "POST",
                "uri" => "en/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'store',
                    'method' => 'POST',
                    'path' => 'en/jobs',
                    'title' => 'Jobs'
                ],
            ],
            "de.jobs.edit" => [
                "method" => "GET",
                "uri" => "de/jobs/{job}/edit",
                "action" => 'RouteTreeTests\Controllers\TestController@edit',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'edit',
                    'method' => 'GET',
                    'path' => 'de/jobs/{job}/edit',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.edit" => [
                "method" => "GET",
                "uri" => "en/jobs/{job}/edit",
                "action" => 'RouteTreeTests\Controllers\TestController@edit',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'edit',
                    'method' => 'GET',
                    'path' => 'en/jobs/{job}/edit',
                    'title' => 'Jobs'
                ],
            ],
            "de.jobs.update" => [
                "method" => "PUT",
                "uri" => "de/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@update',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'update',
                    'method' => 'PUT',
                    'path' => 'de/jobs/{job}',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.update" => [
                "method" => "PUT",
                "uri" => "en/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@update',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'update',
                    'method' => 'PUT',
                    'path' => 'en/jobs/{job}',
                    'title' => 'Jobs'
                ],
            ],
            "de.jobs.destroy" => [
                "method" => "DELETE",
                "uri" => "de/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'destroy',
                    'method' => 'DELETE',
                    'path' => 'de/jobs/{job}',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.destroy" => [
                "method" => "DELETE",
                "uri" => "en/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@destroy',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'destroy',
                    'method' => 'DELETE',
                    'path' => 'en/jobs/{job}',
                    'title' => 'Jobs'
                ],
            ],
            "de.jobs.show" => [
                "method" => "GET",
                "uri" => "de/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@show',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'show',
                    'method' => 'GET',
                    'path' => 'de/jobs/{job}',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.show" => [
                "method" => "GET",
                "uri" => "en/jobs/{job}",
                "action" => 'RouteTreeTests\Controllers\TestController@show',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'show',
                    'method' => 'GET',
                    'path' => 'en/jobs/{job}',
                    'title' => 'Jobs'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
    }

    public function testResourceUsingOnly()
    {

        $this->nodeTree = [
            'jobs' => [
                'resource' => [
                    'name' => 'job',
                    'controller' => 'TestController',
                    'only' => ['index', 'create']
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/jobs',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.index" => [
                "method" => "GET",
                "uri" => "en/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/jobs',
                    'title' => 'Jobs'
                ],
            ],
            "de.jobs.create" => [
                "method" => "GET",
                "uri" => "de/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'de/jobs/create',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'en/jobs/create',
                    'title' => 'Jobs'
                ],
            ],

        ]);

        $this->performFullRoutesTest();
    }


    public function testResourceUsingExcept()
    {

        $this->nodeTree = [
            'jobs' => [
                'resource' => [
                    'name' => 'job',
                    'controller' => 'TestController',
                    'except' => ['show', 'update', 'destroy', 'edit', 'store']
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/jobs',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.index" => [
                "method" => "GET",
                "uri" => "en/jobs",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/jobs',
                    'title' => 'Jobs'
                ],
            ],
            "de.jobs.create" => [
                "method" => "GET",
                "uri" => "de/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'de/jobs/create',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => 'RouteTreeTests\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'en/jobs/create',
                    'title' => 'Jobs'
                ],
            ],

        ]);

        $this->performFullRoutesTest();
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

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.not_inherited.index" => [
                "method" => "GET",
                "uri" => "de/not_inherited",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/not_inherited',
                    'title' => 'Not_inherited'
                ],
            ],
            "en.not_inherited.index" => [
                "method" => "GET",
                "uri" => "en/not_inherited",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/not_inherited',
                    'title' => 'Not_inherited'
                ],
            ],
            "de.not_inherited.child1.index" => [
                "method" => "GET",
                "uri" => "de/child1",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited.child1',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/child1',
                    'title' => 'Child1'
                ],
            ],
            "en.not_inherited.child1.index" => [
                "method" => "GET",
                "uri" => "en/child1",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited.child1',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/child1',
                    'title' => 'Child1'
                ],
            ],
            "de.not_inherited.child2.index" => [
                "method" => "GET",
                "uri" => "de/child2",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited.child2',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/child2',
                    'title' => 'Child2'
                ],
            ],
            "en.not_inherited.child2.index" => [
                "method" => "GET",
                "uri" => "en/child2",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited.child2',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/child2',
                    'title' => 'Child2'
                ],
            ],

        ]);

        $this->performFullRoutesTest();
    }

    public function testAppendNamespace()
    {

        $this->nodeTree = [
            'nested' => [
                'index' => ['uses' => 'NestedController@index'],
                'appendNamespace' => 'Nested'
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.nested.index" => [
                "method" => "GET",
                "uri" => "de/nested",
                "action" => 'RouteTreeTests\Controllers\Nested\NestedController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'nested',
                    'controller' => 'nested',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/nested',
                    'title' => 'Nested'
                ],
            ],
            "en.nested.index" => [
                "method" => "GET",
                "uri" => "en/nested",
                "action" => 'RouteTreeTests\Controllers\Nested\NestedController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'nested',
                    'controller' => 'nested',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/nested',
                    'title' => 'Nested'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
    }

    public function testAutoTranslatedPathAndTitle()
    {

        $this->nodeTree = [
            'products' => [
                'index' => ['uses' => 'TestController@index'],
                'children' => [
                    'product1' => [
                        'index' => ['uses' => 'TestController@index'],
                    ],
                    'product2' => [
                        'index' => ['uses' => 'TestController@index'],
                    ]
                ]
            ],
            'contact' => [
                'index' => ['uses' => 'TestController@index'],
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.products.index" => [
                "method" => "GET",
                "uri" => "de/produkte",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'products',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/produkte',
                    'title' => 'Unsere Produkte'
                ],
            ],
            "en.products.index" => [
                "method" => "GET",
                "uri" => "en/products",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'products',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/products',
                    'title' => 'Our products'
                ],
            ],
            "de.products.product1.index" => [
                "method" => "GET",
                "uri" => "de/produkte/produkt_1",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'products.product1',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/produkte/produkt_1',
                    'title' => 'Produkt 1'
                ],
            ],
            "en.products.product1.index" => [
                "method" => "GET",
                "uri" => "en/products/product_1",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'products.product1',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/products/product_1',
                    'title' => 'Product 1'
                ],
            ],
            "de.products.product2.index" => [
                "method" => "GET",
                "uri" => "de/produkte/produkt_2",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'products.product2',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/produkte/produkt_2',
                    'title' => 'Produkt 2'
                ],
            ],
            "en.products.product2.index" => [
                "method" => "GET",
                "uri" => "en/products/product_2",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'products.product2',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/products/product_2',
                    'title' => 'Product 2'
                ],
            ],
            "de.contact.index" => [
                "method" => "GET",
                "uri" => "de/kontakt",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'contact',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'de/kontakt',
                    'title' => 'Kontaktieren Sie uns'
                ],
            ],
            "en.contact.index" => [
                "method" => "GET",
                "uri" => "en/contact",
                "action" => 'RouteTreeTests\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'contact',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'path' => 'en/contact',
                    'title' => 'Contact us'
                ],
            ]

        ]);

        $this->performFullRoutesTest();
    }


    public function testCustomTitle()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@get'],
                'title' => 'Custom Title'
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/page',
                    'title' => 'Custom Title'
                ],
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/page',
                    'title' => 'Custom Title'
                ],
            ]
        ]);

        $this->performFullRoutesTest();
    }

    public function testCustomTitlePerLanguage()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@get'],
                'title' => [
                    'de' => 'Benutzerdefinierter Titel',
                    'en' => 'Custom Title'
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/page',
                    'title' => 'Benutzerdefinierter Titel'
                ],
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/page',
                    'title' => 'Custom Title'
                ],
            ]
        ]);

        $this->performFullRoutesTest();
    }

    public function testCustomTitleViaClosure()
    {

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@get'],
                'title' => function ($parameters, $language) {
                    if ($language == 'de') {
                        return 'Benutzerdefinierter Titel';
                    } else {
                        return 'Custom Title';
                    }
                }
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/page',
                    'title' => 'Benutzerdefinierter Titel'
                ],
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/page',
                    'title' => 'Custom Title'
                ],
            ]
        ]);

        $this->performFullRoutesTest();
    }


    public function testMiddleware()
    {

        $this->rootNode = [
            'namespace' => 'RouteTreeTests\Controllers',
            'index' => ['uses' => 'TestController@get'],
            'middleware' => [
                'test1' => [
                    'parameters' => [
                        'parameter1' => 'value1',
                        'parameter2' => 'value2'
                    ]
                ],
                'test2' => [
                    'inherit' => false
                ]
            ]
        ];

        $this->nodeTree = [
            'page' => [
                'index' => ['uses' => 'TestController@get'],
                'store' => [
                    'uses' => 'TestController@store',
                    'middleware' => [
                        'test4' => [
                            'parameters' => [
                                'parameter1' => 'value5',
                                'parameter2' => 'value6'
                            ]
                        ]
                    ]
                ],
                'middleware' => [
                    'test3' => [
                        'parameters' => [
                            'parameter1' => 'value3',
                            'parameter2' => 'value4'
                        ]
                    ]
                ]
            ]
        ];

        $this->expectedResult = array_merge($this->expectedResult, [
            "de.index" => [
                "method" => "GET",
                "uri" => "de",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [
                    'test1' => 'test1:value1,value2',
                    'test2' => 'test2'
                ],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de',
                    'title' => 'Startseite'
                ],
            ],
            "en.index" => [
                "method" => "GET",
                "uri" => "en",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [
                    'test1' => 'test1:value1,value2',
                    'test2' => 'test2'
                ],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en',
                    'title' => 'Startpage'
                ],
            ],
            "de.page.index" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [
                    'test1' => 'test1:value1,value2',
                    'test3' => 'test3:value3,value4'
                ],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.index" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@get',
                "middleware" => [
                    'test1' => 'test1:value1,value2',
                    'test3' => 'test3:value3,value4'
                ],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ],
            "de.page.store" => [
                "method" => "POST",
                "uri" => "de/page",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [
                    'test1' => 'test1:value1,value2',
                    'test3' => 'test3:value3,value4',
                    'test4' => 'test4:value5,value6'
                ],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'store',
                    'method' => 'POST',
                    'path' => 'de/page',
                    'title' => 'Page'
                ],
            ],
            "en.page.store" => [
                "method" => "POST",
                "uri" => "en/page",
                "action" => 'RouteTreeTests\Controllers\TestController@store',
                "middleware" => [
                    'test1' => 'test1:value1,value2',
                    'test3' => 'test3:value3,value4',
                    'test4' => 'test4:value5,value6'
                ],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'store',
                    'method' => 'POST',
                    'path' => 'en/page',
                    'title' => 'Page'
                ],
            ],

        ]);

        $this->performFullRoutesTest();
    }

}
