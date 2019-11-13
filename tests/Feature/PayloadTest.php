<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;
use Webflorist\RouteTree\Domain\RouteNode;

class PayloadTest extends TestCase
{

    public function test_node_with_payload()
    {
        $this->routeTree->node('payload', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->onlyLocales(['de']);
            $node->payload->myPayloadString = 'My payload string.';
            $node->payload->myPayloadArray = [
                'my' => 'payload',
                'ar' => 'ray'
            ];
            $node->payload->myPayloadInteger = 42;
            $node->payload->myPayloadBoolean = true;
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.payload.get" => [
                "method" => "GET",
                "uri" => "de/payload",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    "id" => 'payload',
                    'method' => 'GET',
                    'path' => 'de/payload',
                    'controller' => 'test',
                    'function' => 'get',
                    'locale' => 'de',
                    'payload' => [
                        'myPayloadString' => 'My payload string.',
                        'myPayloadArray' => [
                            'my' => 'payload',
                            'ar' => 'ray'
                        ],
                        'myPayloadInteger' => 42,
                        'myPayloadBoolean' => true
                    ],
                    'title' => 'Payload',
                    'navTitle' => 'Payload',
                    'h1Title' => 'Payload'
                ],
            ]

        ]);
    }

    public function test_node_with_translated_payload()
    {
        $this->routeTree->node('payload', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->payload->myPayloadString = [
                'en' => 'My payload string.',
                'de' => 'Mein Payload String.'
            ];
            $node->payload->myPayloadArray = [
                'en' => [
                    'my' => 'payload',
                    'ar' => 'ray'
                ],
                'de' => [
                    'mein' => 'payload',
                    'ar' => 'ray'
                ]
            ];
            $node->payload->myPayloadInteger = [
                'en' => 42,
                'de' => 43,
            ];
            $node->payload->myPayloadBoolean = [
                'en' => true,
                'de' => false
            ];
        });

        $this->routeTree->generateAllRoutes();

        $this->app->setLocale('en');

        $this->assertEquals(
            'My payload string.',
            $this->routeTree->getNode('payload')->payload->trans('myPayloadString')
        );
        $this->assertEquals(
            [
                'my' => 'payload',
                'ar' => 'ray'
            ],
            $this->routeTree->getNode('payload')->payload->trans('myPayloadArray')
        );
        $this->assertEquals(
            42,
            $this->routeTree->getNode('payload')->payload->trans('myPayloadInteger')
        );
        $this->assertEquals(
            true,
            $this->routeTree->getNode('payload')->payload->trans('myPayloadBoolean')
        );

        $this->app->setLocale('de');

        $this->assertEquals(
            'Mein Payload String.',
            $this->routeTree->getNode('payload')->payload->trans('myPayloadString')
        );
        $this->assertEquals(
            [
                'mein' => 'payload',
                'ar' => 'ray'
            ],
            $this->routeTree->getNode('payload')->payload->trans('myPayloadArray')
        );
        $this->assertEquals(
            43,
            $this->routeTree->getNode('payload')->payload->trans('myPayloadInteger')
        );
        $this->assertEquals(
            false,
            $this->routeTree->getNode('payload')->payload->trans('myPayloadBoolean')
        );


    }

    public function test_node_with_translated_closure_payload()
    {
        $this->routeTree->node('payload', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->payload->myPayloadClosure = function (array $parameters, string $locale) {
                return $locale === 'de' ? 'Meine Payload Closure.' : 'My payload closure.';
            };
        });

        $this->routeTree->generateAllRoutes();

        $this->app->setLocale('en');

        $this->assertEquals(
            'My payload closure.',
            $this->routeTree->getNode('payload')->payload->trans('myPayloadClosure')
        );

        $this->app->setLocale('de');

        $this->assertEquals(
            'Meine Payload Closure.',
            $this->routeTree->getNode('payload')->payload->trans('myPayloadClosure')
        );
    }

    public function test_title()
    {
        $this->routeTree->node('page', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
            $node->payload->title = 'Custom Title';
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.page.get" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/page',
                    'payload' => [
                        'title' => 'Custom Title',
                    ],
                    'title' => 'Custom Title',
                    'navTitle' => 'Custom Title',
                    'h1Title' => 'Custom Title'
                ],
            ],
            "en.page.get" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/page',
                    'payload' => [
                        'title' => 'Custom Title',
                    ],
                    'title' => 'Custom Title',
                    'navTitle' => 'Custom Title',
                    'h1Title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_title_per_language()
    {

        $this->routeTree->node('page', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
            $node->payload->title = [
                'de' => 'Benutzerdefinierter Titel',
                'en' => 'Custom Title'
            ];
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.page.get" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/page',
                    'payload' => [
                        'title' => [
                            'de' => 'Benutzerdefinierter Titel',
                            'en' => 'Custom Title'
                        ],
                    ],
                    'title' => 'Benutzerdefinierter Titel',
                    'navTitle' => 'Benutzerdefinierter Titel',
                    'h1Title' => 'Benutzerdefinierter Titel'
                ],
            ],
            "en.page.get" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/page',
                    'payload' => [
                        'title' => [
                            'de' => 'Benutzerdefinierter Titel',
                            'en' => 'Custom Title'
                        ],
                    ],
                    'title' => 'Custom Title',
                    'navTitle' => 'Custom Title',
                    'h1Title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_title_via_closure()
    {

        $this->routeTree->node('page', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
            $node->payload->title = function ($parameters, $language) {
                if ($language == 'de') {
                    return 'Benutzerdefinierter Titel';
                } else {
                    return 'Custom Title';
                }
            };
        });

        $this->routeTree->generateAllRoutes();
        $this->assertRouteTree([
            "de.page.get" => [
                "method" => "GET",
                "uri" => "de/page",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/page',
                    'payload' => [
                        'title' => [],
                    ],
                    'title' => 'Benutzerdefinierter Titel',
                    'navTitle' => 'Benutzerdefinierter Titel',
                    'h1Title' => 'Benutzerdefinierter Titel'
                ],
            ],
            "en.page.get" => [
                "method" => "GET",
                "uri" => "en/page",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/page',
                    'payload' => [
                        'title' => [],
                    ],
                    'title' => 'Custom Title',
                    'navTitle' => 'Custom Title',
                    'h1Title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_titles_per_language_and_action()
    {

        $this->routeTree->node('comment', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@index', 'index');
            $node->get('TestController@create', 'create')->segment('create');
            $node->payload->title = [
                'de' => 'Kommentare',
                'en' => 'Comments'
            ];
            $node->payload->h1Title([
                'de' => 'Liste von Kommentaren',
                'en' => 'List of comments'
            ], 'index');

            $node->payload
                ->set(
                    'title',
                    [
                        'de' => 'Neuen Kommentar erstellen',
                        'en' => 'Add new comment'
                    ],
                    'create'
                )
                ->set(
                    'navTitle',
                    [
                        'de' => 'Kommentar erstellen',
                        'en' => 'Create comment'
                    ],
                    'create'
                );
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.comment.index" => [
                "method" => "GET",
                "uri" => "de/comment",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'comment',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/comment',
                    'payload' => [
                        'title' => [
                            'de' => 'Kommentare',
                            'en' => 'Comments'
                        ]
                    ],
                    'title' => 'Kommentare',
                    'navTitle' => 'Kommentare',
                    'h1Title' => 'Liste von Kommentaren'
                ],
            ],
            "en.comment.index" => [
                "method" => "GET",
                "uri" => "en/comment",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'comment',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/comment',
                    'payload' => [
                        'title' => [
                            'de' => 'Kommentare',
                            'en' => 'Comments'
                        ]
                    ],
                    'title' => 'Comments',
                    'navTitle' => 'Comments',
                    'h1Title' => 'List of comments'
                ],
            ],
            "de.comment.create" => [
                "method" => "GET",
                "uri" => "de/comment/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'comment',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/comment/create',
                    'payload' => [
                        'title' => [
                            'de' => 'Kommentare',
                            'en' => 'Comments'
                        ],
                    ],
                    'title' => 'Neuen Kommentar erstellen',
                    'navTitle' => 'Kommentar erstellen',
                    'h1Title' => 'Neuen Kommentar erstellen'
                ],
            ],
            "en.comment.create" => [
                "method" => "GET",
                "uri" => "en/comment/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'comment',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/comment/create',
                    'payload' => [
                        'title' => [
                            'de' => 'Kommentare',
                            'en' => 'Comments'
                        ],
                    ],
                    'title' => 'Add new comment',
                    'navTitle' => 'Create comment',
                    'h1Title' => 'Add new comment'
                ],
            ]
        ]);

    }


}