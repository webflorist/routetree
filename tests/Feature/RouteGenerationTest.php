<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\Feature\Traits\UsesTestRoutes;
use RouteTreeTests\TestCase;
use Webflorist\RouteTree\LanguageMapping;
use Webflorist\RouteTree\RouteNode;

class RouteGenerationTest extends TestCase
{
    use UsesTestRoutes;

    public function test_root_node_with_closure()
    {
        $this->routeTree->root(function (RouteNode $rootNode) {
            $rootNode->middleware('web');
            $rootNode->get(function () {
                return TestCase::getRouteTestData();
            });

        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
                "method" => "GET",
                "uri" => "de",
                "action" => "Closure",
                "middleware" => ['web' => 'web'],
                "content" => [
                    'id' => '',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "en.get" => [
                "method" => "GET",
                "uri" => "en",
                "action" => "Closure",
                "middleware" => ['web' => 'web'],
                "content" => [
                    'id' => '',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage'
                ],
            ]

        ]);
    }

    public function test_root_node_with_controller_action_and_all_methods()
    {
        $this->routeTree->root(function (RouteNode $rootNode) {
            $rootNode->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $rootNode->post('\RouteTreeTests\Feature\Controllers\TestController@post');
            $rootNode->put('\RouteTreeTests\Feature\Controllers\TestController@put');
            $rootNode->patch('\RouteTreeTests\Feature\Controllers\TestController@patch');
            $rootNode->delete('\RouteTreeTests\Feature\Controllers\TestController@delete');
            $rootNode->options('\RouteTreeTests\Feature\Controllers\TestController@options');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
                "method" => "GET",
                "uri" => "de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "de.post" => [
                "method" => "POST",
                "uri" => "de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@post',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'post',
                    'method' => 'POST',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "de.put" => [
                "method" => "PUT",
                "uri" => "de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@put',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'put',
                    'method' => 'PUT',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "de.patch" => [
                "method" => "PATCH",
                "uri" => "de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@patch',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'patch',
                    'method' => 'PATCH',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "de.delete" => [
                "method" => "DELETE",
                "uri" => "de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@delete',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'delete',
                    'method' => 'DELETE',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "de.options" => [
                "method" => "OPTIONS",
                "uri" => "de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@options',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'options',
                    'method' => 'OPTIONS',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "en.get" => [
                "method" => "GET",
                "uri" => "en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage'
                ],
            ],
            "en.post" => [
                "method" => "POST",
                "uri" => "en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@post',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'post',
                    'method' => 'POST',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage'
                ],
            ],
            "en.put" => [
                "method" => "PUT",
                "uri" => "en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@put',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'put',
                    'method' => 'PUT',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage'
                ],
            ],
            "en.patch" => [
                "method" => "PATCH",
                "uri" => "en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@patch',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'patch',
                    'method' => 'PATCH',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage'
                ],
            ],
            "en.delete" => [
                "method" => "DELETE",
                "uri" => "en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@delete',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'delete',
                    'method' => 'DELETE',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage'
                ],
            ],
            "en.options" => [
                "method" => "OPTIONS",
                "uri" => "en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@options',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'options',
                    'method' => 'OPTIONS',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage'
                ],
            ],
        ]);
    }

    public function test_root_node_with_view()
    {
        $this->routeTree->root(function (RouteNode $rootNode) {
            $rootNode->view('test', ['foo' => 'bar']);
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
                "method" => "GET",
                "uri" => "de",
                "action" => '\Illuminate\Routing\ViewController',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite',
                    'view' => 'test',
                    'foo' => 'bar'
                ],
            ],
            "en.get" => [
                "method" => "GET",
                "uri" => "en",
                "action" => '\Illuminate\Routing\ViewController',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage',
                    'view' => 'test',
                    'foo' => 'bar'
                ],
            ]
        ]);
    }

    public function test_root_node_with_redirect()
    {
        $this->routeTree->root()->redirect('destination');

        $this->routeTree->node('destination', function (RouteNode $rootNode) {
            $rootNode->view('test');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "de",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                'redirectTarget' => '/de/destination',
                "statusCode" => 302
            ],
            "en.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "en",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                'redirectTarget' => '/en/destination',
                "statusCode" => 302
            ],
            "de.destination.get" => [
                "method" => "GET",
                "uri" => "de/destination",
                "action" => '\Illuminate\Routing\ViewController',
                "middleware" => [],
                "content" => [
                    'id' => 'destination',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/destination',
                    'navTitle' => 'Destination',
                    'h1Title' => 'Destination',
                    'title' => 'Destination',
                    'view' => 'test',
                    'foo' => null
                ],
            ],
            "en.destination.get" => [
                "method" => "GET",
                "uri" => "en/destination",
                "action" => '\Illuminate\Routing\ViewController',
                "middleware" => [],
                "content" => [
                    'id' => 'destination',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/destination',
                    'navTitle' => 'Destination',
                    'h1Title' => 'Destination',
                    'title' => 'Destination',
                    'view' => 'test',
                    'foo' => null
                ],
            ]
        ]);
    }

    public function test_root_node_with_permanent_redirect()
    {
        $this->routeTree->root(function (RouteNode $rootNode) {
            $rootNode->permanentRedirect('destination');
        });

        $this->routeTree->node('destination', function (RouteNode $rootNode) {
            $rootNode->view('test');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "de",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                'redirectTarget' => '/de/destination',
                "statusCode" => 301
            ],
            "en.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "en",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                'redirectTarget' => '/en/destination',
                "statusCode" => 301
            ],
            "de.destination.get" => [
                "method" => "GET",
                "uri" => "de/destination",
                "action" => '\Illuminate\Routing\ViewController',
                "middleware" => [],
                "content" => [
                    'id' => 'destination',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/destination',
                    'navTitle' => 'Destination',
                    'h1Title' => 'Destination',
                    'title' => 'Destination',
                    'view' => 'test',
                    'foo' => null
                ],
            ],
            "en.destination.get" => [
                "method" => "GET",
                "uri" => "en/destination",
                "action" => '\Illuminate\Routing\ViewController',
                "middleware" => [],
                "content" => [
                    'id' => 'destination',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/destination',
                    'navTitle' => 'Destination',
                    'h1Title' => 'Destination',
                    'title' => 'Destination',
                    'view' => 'test',
                    'foo' => null
                ],
            ]
        ]);
    }

    public function test_cross_branch_redirect()
    {
        route_tree()->root(function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');

            $node->child('page1', function (RouteNode $node) {
                $node->get('TestController@get');

                $node->child('page1-1', function (RouteNode $node) {
                    $node->get('TestController@get');
                });

            });

            $node->child('page2', function (RouteNode $node) {
                $node->get('TestController@get');

                $node->child('page2-1', function (RouteNode $node) {
                    $node->redirect('page1.page1-1');
                });

            });
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.page1.get" => [
                "method" => "GET",
                "uri" => "de/page1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page1',
                    'method' => 'GET',
                    'locale' => 'de',
                    'controller' => 'test',
                    'function' => 'get',
                    'path' => 'de/page1',
                    'navTitle' => 'Page1',
                    'h1Title' => 'Page1',
                    'title' => 'Page1'
                ],
            ],
            "en.page1.get" => [
                "method" => "GET",
                "uri" => "en/page1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page1',
                    'method' => 'GET',
                    'locale' => 'en',
                    'controller' => 'test',
                    'function' => 'get',
                    'path' => 'en/page1',
                    'navTitle' => 'Page1',
                    'h1Title' => 'Page1',
                    'title' => 'Page1'
                ],
            ],
            "de.page1.page1-1.get" => [
                "method" => "GET",
                "uri" => "de/page1/page1-1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page1.page1-1',
                    'method' => 'GET',
                    'locale' => 'de',
                    'controller' => 'test',
                    'function' => 'get',
                    'path' => 'de/page1/page1-1',
                    'navTitle' => 'Page1-1',
                    'h1Title' => 'Page1-1',
                    'title' => 'Page1-1'
                ],
            ],
            "en.page1.page1-1.get" => [
                "method" => "GET",
                "uri" => "en/page1/page1-1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page1.page1-1',
                    'method' => 'GET',
                    'locale' => 'en',
                    'controller' => 'test',
                    'function' => 'get',
                    'path' => 'en/page1/page1-1',
                    'navTitle' => 'Page1-1',
                    'h1Title' => 'Page1-1',
                    'title' => 'Page1-1'
                ],
            ],

            "de.page2.get" => [
                "method" => "GET",
                "uri" => "de/page2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page2',
                    'method' => 'GET',
                    'locale' => 'de',
                    'controller' => 'test',
                    'function' => 'get',
                    'path' => 'de/page2',
                    'navTitle' => 'Page2',
                    'h1Title' => 'Page2',
                    'title' => 'Page2'
                ],
            ],
            "en.page2.get" => [
                "method" => "GET",
                "uri" => "en/page2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page2',
                    'method' => 'GET',
                    'locale' => 'en',
                    'controller' => 'test',
                    'function' => 'get',
                    'path' => 'en/page2',
                    'navTitle' => 'Page2',
                    'h1Title' => 'Page2',
                    'title' => 'Page2'
                ],
            ],
            "de.page2.page2-1.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "de/page2/page2-1",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                'redirectTarget' => '/de/page1/page1-1',
                "statusCode" => 302
            ],
            "en.page2.page2-1.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "en/page2/page2-1",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                'redirectTarget' => '/en/page1/page1-1',
                "statusCode" => 302
            ],
        ]);

        $this->assertJsonResponse("de/page2/page2-1",
            [
                'id' => 'page1.page1-1',
                'method' => 'GET',
                'locale' => 'de',
                'controller' => 'test',
                'function' => 'get',
                'path' => 'de/page1/page1-1',
                'navTitle' => 'Page1-1',
                'h1Title' => 'Page1-1',
                'title' => 'Page1-1'
            ],
            true
        );
    }


    public function test_node_with_children()
    {

        $this->routeTree->node('parent', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');

            $node->get('TestController@get');

            $node->child('child1', function (RouteNode $node) {
                $node->get('TestController@get');
            });

            $node->child('child2', function (RouteNode $node) {
                $node->get('TestController@get');
            });
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.parent.get" => [
                "method" => "GET",
                "uri" => "de/parent",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/parent',
                    'navTitle' => 'Parent',
                    'h1Title' => 'Parent',
                    'title' => 'Parent'
                ],
            ],
            "en.parent.get" => [
                "method" => "GET",
                "uri" => "en/parent",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/parent',
                    'navTitle' => 'Parent',
                    'h1Title' => 'Parent',
                    'title' => 'Parent'
                ],
            ],
            "de.parent.child1.get" => [
                "method" => "GET",
                "uri" => "de/parent/child1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/parent/child1',
                    'navTitle' => 'Child1',
                    'h1Title' => 'Child1',
                    'title' => 'Child1'
                ],
            ],
            "en.parent.child1.get" => [
                "method" => "GET",
                "uri" => "en/parent/child1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/parent/child1',
                    'navTitle' => 'Child1',
                    'h1Title' => 'Child1',
                    'title' => 'Child1'
                ],
            ],
            "de.parent.child2.get" => [
                "method" => "GET",
                "uri" => "de/parent/child2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/parent/child2',
                    'navTitle' => 'Child2',
                    'h1Title' => 'Child2',
                    'title' => 'Child2'
                ],
            ],
            "en.parent.child2.get" => [
                "method" => "GET",
                "uri" => "en/parent/child2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/parent/child2',
                    'navTitle' => 'Child2',
                    'h1Title' => 'Child2',
                    'title' => 'Child2'
                ],
            ],

        ]);
    }

    public function test_node_with_children_without_locale_in_path()
    {
        $this->config->set('routetree.locales', null);
        $this->config->set('app.locale', 'de');

        $this->routeTree->root(function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
            $node->child('parent', function (RouteNode $node) {
                $node->get('TestController@get');
                $node->child('child1', function (RouteNode $node) {
                    $node->get('TestController@get');
                });

                $node->child('child2', function (RouteNode $node) {
                    $node->get('TestController@get');
                });
            });
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
                "method" => "GET",
                "uri" => "/",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => '',
                    'locale' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "de.parent.get" => [
                "method" => "GET",
                "uri" => "parent",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'parent',
                    'navTitle' => 'Parent',
                    'h1Title' => 'Parent',
                    'title' => 'Parent'
                ],
            ],
            "de.parent.child1.get" => [
                "method" => "GET",
                "uri" => "parent/child1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'parent/child1',
                    'navTitle' => 'Child1',
                    'h1Title' => 'Child1',
                    'title' => 'Child1'
                ],
            ],
            "de.parent.child2.get" => [
                "method" => "GET",
                "uri" => "parent/child2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'parent/child2',
                    'navTitle' => 'Child2',
                    'h1Title' => 'Child2',
                    'title' => 'Child2'
                ],
            ],

        ]);
    }

    public function test_node_with_one_child_set_to_no_locale_prefix()
    {

        $this->routeTree->root(function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
            $node->child('parent', function (RouteNode $node) {
                $node->get('TestController@get');
                $node->child('child1', function (RouteNode $node) {
                    $node->get('TestController@get');
                });

                $node->child('child2', function (RouteNode $node) {
                    $node->noLocalePrefix();
                    $node->get('TestController@get');
                });
            });
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
                "method" => "GET",
                "uri" => "de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de',
                    'navTitle' => 'Startseite',
                    'h1Title' => 'Startseite',
                    'title' => 'Startseite'
                ],
            ],
            "en.get" => [
                "method" => "GET",
                "uri" => "en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => '',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en',
                    'navTitle' => 'Startpage',
                    'h1Title' => 'Startpage',
                    'title' => 'Startpage'
                ],
            ],
            "de.parent.get" => [
                "method" => "GET",
                "uri" => "de/parent",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/parent',
                    'navTitle' => 'Parent',
                    'h1Title' => 'Parent',
                    'title' => 'Parent'
                ],
            ],
            "en.parent.get" => [
                "method" => "GET",
                "uri" => "en/parent",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/parent',
                    'navTitle' => 'Parent',
                    'h1Title' => 'Parent',
                    'title' => 'Parent'
                ],
            ],
            "de.parent.child1.get" => [
                "method" => "GET",
                "uri" => "de/parent/child1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/parent/child1',
                    'navTitle' => 'Child1',
                    'h1Title' => 'Child1',
                    'title' => 'Child1'
                ],
            ],
            "en.parent.child1.get" => [
                "method" => "GET",
                "uri" => "en/parent/child1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/parent/child1',
                    'navTitle' => 'Child1',
                    'h1Title' => 'Child1',
                    'title' => 'Child1'
                ],
            ],
            "de.parent.child2.get" => [
                "method" => "GET",
                "uri" => "parent/child2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'parent/child2',
                    'navTitle' => 'Child2',
                    'h1Title' => 'Child2',
                    'title' => 'Child2'
                ],
            ]

        ]);
    }


    public function test_custom_segment()
    {
        $this->routeTree->node('page', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->segment('custom-segment');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.page.get" => [
                "method" => "GET",
                "uri" => "de/custom-segment",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/custom-segment',
                    'navTitle' => 'Page',
                    'h1Title' => 'Page',
                    'title' => 'Page'
                ],
            ],
            "en.page.get" => [
                "method" => "GET",
                "uri" => "en/custom-segment",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/custom-segment',
                    'navTitle' => 'Page',
                    'h1Title' => 'Page',
                    'title' => 'Page'
                ],
            ]

        ]);
    }

    public function test_custom_segment_per_language()
    {

        $this->routeTree->node('page', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->segment(LanguageMapping::create()
                ->set('de', 'custom-segment-de')
                ->set('en', 'custom-segment-en')
            );
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.page.get" => [
                "method" => "GET",
                "uri" => "de/custom-segment-de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/custom-segment-de',
                    'navTitle' => 'Page',
                    'h1Title' => 'Page',
                    'title' => 'Page'
                ],
            ],
            "en.page.get" => [
                "method" => "GET",
                "uri" => "en/custom-segment-en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'page',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/custom-segment-en',
                    'navTitle' => 'Page',
                    'h1Title' => 'Page',
                    'title' => 'Page'
                ],
            ]

        ]);
    }

    public function test_no_inherit_segment()
    {

        $this->routeTree->node('not_inherited', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->inheritSegment(false);
            $node->get('TestController@get');
            $node->child('child1', function (RouteNode $node) {
                $node->get('TestController@get');
            });
            $node->child('child2', function (RouteNode $node) {
                $node->get('TestController@get');
            });
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.not_inherited.get" => [
                "method" => "GET",
                "uri" => "de/not_inherited",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/not_inherited',
                    'navTitle' => 'Not_inherited',
                    'h1Title' => 'Not_inherited',
                    'title' => 'Not_inherited'
                ],
            ],
            "en.not_inherited.get" => [
                "method" => "GET",
                "uri" => "en/not_inherited",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/not_inherited',
                    'navTitle' => 'Not_inherited',
                    'h1Title' => 'Not_inherited',
                    'title' => 'Not_inherited'
                ],
            ],
            "de.not_inherited.child1.get" => [
                "method" => "GET",
                "uri" => "de/child1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/child1',
                    'navTitle' => 'Child1',
                    'h1Title' => 'Child1',
                    'title' => 'Child1'
                ],
            ],
            "en.not_inherited.child1.get" => [
                "method" => "GET",
                "uri" => "en/child1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited.child1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/child1',
                    'navTitle' => 'Child1',
                    'h1Title' => 'Child1',
                    'title' => 'Child1'
                ],
            ],
            "de.not_inherited.child2.get" => [
                "method" => "GET",
                "uri" => "de/child2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/child2',
                    'navTitle' => 'Child2',
                    'h1Title' => 'Child2',
                    'title' => 'Child2'
                ],
            ],
            "en.not_inherited.child2.get" => [
                "method" => "GET",
                "uri" => "en/child2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'not_inherited.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/child2',
                    'navTitle' => 'Child2',
                    'h1Title' => 'Child2',
                    'title' => 'Child2'
                ],
            ],

        ]);
    }

    public function test_append_namespace()
    {

        $this->routeTree->root(function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->child('nested', function (RouteNode $node) {
                $node->namespace('Nested');
                $node->get('NestedController@get');
            });
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.nested.get" => [
                "method" => "GET",
                "uri" => "de/nested",
                "action" => '\RouteTreeTests\Feature\Controllers\Nested\NestedController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'nested',
                    'controller' => 'nested',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/nested',
                    'navTitle' => 'Nested',
                    'h1Title' => 'Nested',
                    'title' => 'Nested'
                ],
            ],
            "en.nested.get" => [
                "method" => "GET",
                "uri" => "en/nested",
                "action" => '\RouteTreeTests\Feature\Controllers\Nested\NestedController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'nested',
                    'controller' => 'nested',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/nested',
                    'navTitle' => 'Nested',
                    'h1Title' => 'Nested',
                    'title' => 'Nested'
                ],
            ]

        ]);

    }


    public function test_override_namespace()
    {

        $this->routeTree->root(function (RouteNode $node) {
            $node->namespace('\I\Do\Not\Exist');
            $node->child('overridden-namespace', function (RouteNode $node) {
                $node->namespace('\RouteTreeTests\Feature\Controllers');
                $node->get('TestController@get');
            });
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.overridden-namespace.get" => [
                "method" => "GET",
                "uri" => "de/overridden-namespace",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'overridden-namespace',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/overridden-namespace',
                    'navTitle' => 'Overridden-namespace',
                    'h1Title' => 'Overridden-namespace',
                    'title' => 'Overridden-namespace'
                ],
            ],
            "en.overridden-namespace.get" => [
                "method" => "GET",
                "uri" => "en/overridden-namespace",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'overridden-namespace',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/overridden-namespace',
                    'navTitle' => 'Overridden-namespace',
                    'h1Title' => 'Overridden-namespace',
                    'title' => 'Overridden-namespace'
                ],
            ]

        ]);

    }


    public function test_auto_translated_path_and_title()
    {

        $this->routeTree->node('products', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
            $node->child('product1', function (RouteNode $node) {
                $node->get('TestController@get');
            });
            $node->child('product2', function (RouteNode $node) {
                $node->get('TestController@get');
            });
        });

        $this->routeTree->node('contact', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.products.get" => [
                "method" => "GET",
                "uri" => "de/produkte",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'products',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/produkte',
                    'navTitle' => 'Unsere Produkte',
                    'h1Title' => 'Unsere Produkte',
                    'title' => 'Unsere Produkte'
                ],
            ],
            "en.products.get" => [
                "method" => "GET",
                "uri" => "en/products",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'products',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/products',
                    'navTitle' => 'Our products',
                    'h1Title' => 'Our products',
                    'title' => 'Our products'
                ],
            ],
            "de.products.product1.get" => [
                "method" => "GET",
                "uri" => "de/produkte/produkt_1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'products.product1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/produkte/produkt_1',
                    'navTitle' => 'Produkt 1',
                    'h1Title' => 'Produkt 1',
                    'title' => 'Produkt 1'
                ],
            ],
            "en.products.product1.get" => [
                "method" => "GET",
                "uri" => "en/products/product_1",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'products.product1',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/products/product_1',
                    'navTitle' => 'Product 1',
                    'h1Title' => 'Product 1',
                    'title' => 'Product 1'
                ],
            ],
            "de.products.product2.get" => [
                "method" => "GET",
                "uri" => "de/produkte/produkt_2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'products.product2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/produkte/produkt_2',
                    'navTitle' => 'Produkt 2',
                    'h1Title' => 'Produkt 2',
                    'title' => 'Produkt 2'
                ],
            ],
            "en.products.product2.get" => [
                "method" => "GET",
                "uri" => "en/products/product_2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'products.product2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/products/product_2',
                    'navTitle' => 'Product 2',
                    'h1Title' => 'Product 2',
                    'title' => 'Product 2'
                ],
            ],
            "de.contact.get" => [
                "method" => "GET",
                "uri" => "de/kontakt",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'contact',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/kontakt',
                    'navTitle' => 'Kontaktieren Sie uns',
                    'h1Title' => 'Kontaktieren Sie uns',
                    'title' => 'Kontaktieren Sie uns'
                ],
            ],
            "en.contact.get" => [
                "method" => "GET",
                "uri" => "en/contact",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'contact',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/contact',
                    'navTitle' => 'Contact us',
                    'h1Title' => 'Contact us',
                    'title' => 'Contact us'
                ],
            ]

        ]);
    }

    public function test_node_with_parameter_and_where()
    {
        $this->routeTree->node('my-node', function (RouteNode $node) {
            $node->parameter('foobar')->regex('[0-9]+');
            $node->get(function () {
                return 'success';
            });
            $node->post(function () {
                return 'success';
            });
        });

        $this->routeTree->generateAllRoutes();

        $this->get('de/123')->assertStatus(200)->assertSee('success');
        $this->get('de/abc')->assertStatus(404);
        $this->post('de/123')->assertStatus(200)->assertSee('success');
        $this->post('de/abc')->assertStatus(404);

    }

    public function test_node_with_middleware()
    {
        $this->routeTree->node('my-node', function (RouteNode $node) {
            $node->middleware('test1', ['is-inherited', 'is-skipped-by-post-action']);
            $node->middleware('test2', ['is-inherited-but-skipped-by-child'], true);
            $node->middleware('test3', ['is-not-inherited'], false);
            $node->get(function () {
                return 'success';
            });
            $node->post(function () {
                return 'success';
            })->skipMiddleware('test1');

            $node->child('my-child-node', function (RouteNode $node) {
                $node->middleware('test4', ['is-only-on-child']);
                $node->skipMiddleware('test2');
                $node->get(function () {
                    return 'success';
                });
            });
        });

        $this->routeTree->generateAllRoutes();


        $this->assertRouteTree([
            "de.my-node.get" => [
                "method" => "GET",
                "uri" => "de/my-node",
                "action" => 'Closure',
                "middleware" => [
                    'test1' => 'test1:is-inherited,is-skipped-by-post-action',
                    'test2' => 'test2:is-inherited-but-skipped-by-child',
                    'test3' => 'test3:is-not-inherited'
                ],
                "content" => 'success',
            ],
            "en.my-node.get" => [
                "method" => "GET",
                "uri" => "en/my-node",
                "action" => 'Closure',
                "middleware" => [
                    'test1' => 'test1:is-inherited,is-skipped-by-post-action',
                    'test2' => 'test2:is-inherited-but-skipped-by-child',
                    'test3' => 'test3:is-not-inherited'
                ],
                "content" => 'success',
            ],
            "de.my-node.post" => [
                "method" => "POST",
                "uri" => "de/my-node",
                "action" => 'Closure',
                "middleware" => [
                    'test2' => 'test2:is-inherited-but-skipped-by-child',
                    'test3' => 'test3:is-not-inherited'
                ],
                "content" => 'success',
            ],
            "en.my-node.post" => [
                "method" => "POST",
                "uri" => "en/my-node",
                "action" => 'Closure',
                "middleware" => [
                    'test2' => 'test2:is-inherited-but-skipped-by-child',
                    'test3' => 'test3:is-not-inherited'
                ],
                "content" => 'success',
            ],

            "de.my-node.my-child-node.get" => [
                "method" => "GET",
                "uri" => "de/my-node/my-child-node",
                "action" => 'Closure',
                "middleware" => [
                    'test1' => 'test1:is-inherited,is-skipped-by-post-action',
                    'test4' => 'test4:is-only-on-child'
                ],
                "content" => 'success',
            ],
            "en.my-node.my-child-node.get" => [
                "method" => "GET",
                "uri" => "en/my-node/my-child-node",
                "action" => 'Closure',
                "middleware" => [
                    'test1' => 'test1:is-inherited,is-skipped-by-post-action',
                    'test4' => 'test4:is-only-on-child'
                ],
                "content" => 'success',
            ]
        ]);

    }

    public function test_node_with_only_locales()
    {
        $this->routeTree->node('only-de', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->onlyLocales(['de']);
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.only-de.get" => [
                "method" => "GET",
                "uri" => "de/only-de",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    "id" => 'only-de',
                    'method' => 'GET',
                    'path' => 'de/only-de',
                    'controller' => 'test',
                    'function' => 'get',
                    'locale' => 'de',
                    'title' => 'Only-de',
                    'navTitle' => 'Only-de',
                    'h1Title' => 'Only-de'
                ],
            ]

        ]);
    }

    public function test_node_with_except_locales()
    {
        $this->routeTree->node('except-en', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->exceptLocales(['en']);
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.except-en.get" => [
                "method" => "GET",
                "uri" => "de/except-en",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    "id" => 'except-en',
                    'method' => 'GET',
                    'path' => 'de/except-en',
                    'controller' => 'test',
                    'function' => 'get',
                    'locale' => 'de',
                    'title' => 'Except-en',
                    'navTitle' => 'Except-en',
                    'h1Title' => 'Except-en'
                ],
            ]

        ]);
    }

    public function test_node_with_no_locale_prefix_set()
    {

        $this->routeTree->node('de-only-with-no-locale-prefix', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->onlyLocales(['de']);
            $node->noLocalePrefix();
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.de-only-with-no-locale-prefix.get" => [
                "method" => "GET",
                "uri" => "de-only-with-no-locale-prefix",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    "id" => 'de-only-with-no-locale-prefix',
                    'method' => 'GET',
                    'path' => 'de-only-with-no-locale-prefix',
                    'controller' => 'test',
                    'function' => 'get',
                    'locale' => 'de',
                    'title' => 'De-only-with-no-locale-prefix',
                    'navTitle' => 'De-only-with-no-locale-prefix',
                    'h1Title' => 'De-only-with-no-locale-prefix'
                ],
            ]

        ]);
    }

    public function test_node_with_de_as_no_prefix_locales()
    {

        config()->set('routetree.no_prefix_locales', ['de']);

        $this->routeTree->node('de-with-no-locale-prefix', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.de-with-no-locale-prefix.get" => [
                "method" => "GET",
                "uri" => "de-with-no-locale-prefix",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    "id" => 'de-with-no-locale-prefix',
                    'method' => 'GET',
                    'path' => 'de-with-no-locale-prefix',
                    'controller' => 'test',
                    'function' => 'get',
                    'locale' => 'de',
                    'title' => 'De-with-no-locale-prefix',
                    'navTitle' => 'De-with-no-locale-prefix',
                    'h1Title' => 'De-with-no-locale-prefix'
                ],
            ],
            "en.de-with-no-locale-prefix.get" => [
                "method" => "GET",
                "uri" => "en/de-with-no-locale-prefix",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    "id" => 'de-with-no-locale-prefix',
                    'method' => 'GET',
                    'path' => 'en/de-with-no-locale-prefix',
                    'controller' => 'test',
                    'function' => 'get',
                    'locale' => 'en',
                    'title' => 'De-with-no-locale-prefix',
                    'navTitle' => 'De-with-no-locale-prefix',
                    'h1Title' => 'De-with-no-locale-prefix'
                ],
            ]

        ]);
    }

    public function test_complex_routes()
    {
        $this->generateComplexTestRoutes($this->routeTree);

        $this->assertComplexRegisteredRoutes();

        $this->assertComplexTestRouteTree();
    }

    public function test_redirect_to_younger_sibling() {

        route_tree()->node('redirect-to-sibling', function (RouteNode $node) {
            $node->redirect('redirect-target');
        });

        route_tree()->node('redirect-target', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.redirect-target.get" => [
                "method" => "GET",
                "uri" => "de/redirect-target",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    "id" => 'redirect-target',
                    'method' => 'GET',
                    'path' => 'de/redirect-target',
                    'controller' => 'test',
                    'function' => 'get',
                    'locale' => 'de',
                    'title' => 'Redirect-target',
                    'navTitle' => 'Redirect-target',
                    'h1Title' => 'Redirect-target'
                ],
            ],
            "de.redirect-to-sibling.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "de/redirect-to-sibling",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                'statusCode' => 302,
                'redirectTarget' => '/de/redirect-target'
            ],
            "en.redirect-target.get" => [
                "method" => "GET",
                "uri" => "en/redirect-target",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    "id" => 'redirect-target',
                    'method' => 'GET',
                    'path' => 'en/redirect-target',
                    'controller' => 'test',
                    'function' => 'get',
                    'locale' => 'en',
                    'title' => 'Redirect-target',
                    'navTitle' => 'Redirect-target',
                    'h1Title' => 'Redirect-target'
                ],
            ],
            "en.redirect-to-sibling.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "en/redirect-to-sibling",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                'statusCode' => 302,
                'redirectTarget' => '/en/redirect-target'
            ]

        ]);
    }

}