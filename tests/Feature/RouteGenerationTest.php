<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;
use Webflorist\RouteTree\Domain\RouteNode;

class RouteGenerationTest extends TestCase
{

    public function test_root_node_with_closure()
    {
        $this->routeTree->root(function (RouteNode $rootNode) {

            $rootNode->get(function() {
                return json_encode([
                    'id' => route_tree()->getCurrentNode()->getId(),
                    'method' => \Request::getMethod(),
                    'path' => trim(\Request::getPathInfo(),'/'),
                    'title' => route_tree()->getCurrentNode()->getTitle()
                ]);
            });

        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
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
            "en.get" => [
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
                    'path' => 'de',
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
                    'path' => 'de',
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
                    'path' => 'de',
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
                    'path' => 'de',
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
                    'path' => 'de',
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
                    'path' => 'de',
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
                    'path' => 'en',
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
                    'path' => 'en',
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
                    'path' => 'en',
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
                    'path' => 'en',
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
                    'path' => 'en',
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
                    'path' => 'en',
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
                    'path' => 'de',
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
                    'path' => 'en',
                    'title' => 'Startpage',
                    'view' => 'test',
                    'foo' => 'bar'
                ],
            ]
        ]);
    }

    public function test_root_node_with_redirect()
    {
        $this->routeTree->root(function (RouteNode $rootNode) {
            $rootNode->redirect('destination');
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
                "redirectTarget" => 'de/destination',
                "statusCode" => 302
            ],
            "en.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "en",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                "redirectTarget" => 'en/destination',
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
                    'path' => 'de/destination',
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
                    'path' => 'en/destination',
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
                "redirectTarget" => 'de/destination',
                "statusCode" => 301
            ],
            "en.get" => [
                "method" => "GET|POST|PUT|PATCH|DELETE|OPTIONS",
                "uri" => "en",
                "action" => '\Illuminate\Routing\RedirectController',
                "middleware" => [],
                "redirectTarget" => 'en/destination',
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
                    'path' => 'de/destination',
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
                    'path' => 'en/destination',
                    'title' => 'Destination',
                    'view' => 'test',
                    'foo' => null
                ],
            ]
        ]);
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
                    'path' => 'de/parent',
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
                    'path' => 'en/parent',
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
                    'path' => 'de/parent/child1',
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
                    'path' => 'en/parent/child1',
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
                    'path' => 'de/parent/child2',
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
                    'path' => 'en/parent/child2',
                    'title' => 'Child2'
                ],
            ],

        ]);
    }

    public function test_node_with_children_without_locale_in_path()
    {

        $this->config->set('routetree.no_locale_prefix', true);
        $this->config->set('routetree.locales', []);
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
                    'path' => 'parent',
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
                    'path' => 'parent/child1',
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
                    'path' => 'parent/child2',
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
                    'path' => 'de',
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
                    'path' => 'en',
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
                    'path' => 'de/parent',
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
                    'path' => 'en/parent',
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
                    'path' => 'de/parent/child1',
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
                    'path' => 'en/parent/child1',
                    'title' => 'Child1'
                ],
            ],
            "en.parent.child2.get" => [
                "method" => "GET",
                "uri" => "parent/child2",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@get',
                "middleware" => [],
                "content" => [
                    'id' => 'parent.child2',
                    'controller' => 'test',
                    'function' => 'get',
                    'method' => 'GET',
                    'path' => 'parent/child2',
                    'title' => 'Child2'
                ],
            ]

        ]);
    }


    public function test_custom_segment()
    {
        $this->routeTree->node('page', function(RouteNode $node) {
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
                    'path' => 'de/custom-segment',
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
                    'path' => 'en/custom-segment',
                    'title' => 'Page'
                ],
            ]

        ]);
    }

    public function test_custom_segment_per_language()
    {

        $this->routeTree->node('page', function(RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->segment([
                'de' => 'custom-segment-de',
                'en' => 'custom-segment-en'
            ]);
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
                    'path' => 'de/custom-segment-de',
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
                    'path' => 'en/custom-segment-en',
                    'title' => 'Page'
                ],
            ]

        ]);
    }


    public function test_resource()
    {

        $this->routeTree->node('jobs', function(RouteNode $node) {
            $node->resource('job','\RouteTreeTests\Feature\Controllers\TestController');
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
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
                "uri" => "de/jobs/erstellen",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'de/jobs/erstellen',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@store',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@store',
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
                "uri" => "de/jobs/{job}/bearbeiten",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@edit',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'edit',
                    'method' => 'GET',
                    'path' => 'de/jobs/{job}/bearbeiten',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.edit" => [
                "method" => "GET",
                "uri" => "en/jobs/{job}/edit",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@edit',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@update',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@update',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@destroy',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@destroy',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@show',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@show',
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
    }


    public function test_resource_using_only()
    {

        $this->routeTree->node('jobs', function(RouteNode $node) {
            $node->resource('job','\RouteTreeTests\Feature\Controllers\TestController')->only([
                'index', 'create'
            ]);
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
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
                "uri" => "de/jobs/erstellen",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'de/jobs/erstellen',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
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
    }


    public function test_resource_using_except()
    {
        $this->routeTree->node('jobs', function(RouteNode $node) {
            $node->resource('job','\RouteTreeTests\Feature\Controllers\TestController')->except([
                'show', 'update', 'destroy', 'edit', 'store'
            ]);
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.jobs.index" => [
                "method" => "GET",
                "uri" => "de/jobs",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
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
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
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
                "uri" => "de/jobs/erstellen",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'jobs',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'path' => 'de/jobs/erstellen',
                    'title' => 'Jobs'
                ],
            ],
            "en.jobs.create" => [
                "method" => "GET",
                "uri" => "en/jobs/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
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
    }

    public function test_no_inherit_segment()
    {

        $this->routeTree->node('not_inherited', function(RouteNode $node) {
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
                    'path' => 'de/not_inherited',
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
                    'path' => 'en/not_inherited',
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
                    'path' => 'de/child1',
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
                    'path' => 'en/child1',
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
                    'path' => 'de/child2',
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
                    'path' => 'en/child2',
                    'title' => 'Child2'
                ],
            ],

        ]);
    }

    public function test_append_namespace()
    {

        $this->routeTree->root(function(RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->child('nested', function (RouteNode $node) {
                $node->namespace('Nested');
                $node->get( 'NestedController@get');
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
                    'path' => 'de/nested',
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
                    'path' => 'en/nested',
                    'title' => 'Nested'
                ],
            ]

        ]);

    }


    public function test_override_namespace()
    {

        $this->routeTree->root(function(RouteNode $node) {
            $node->namespace('\I\Do\Not\Exist');
            $node->child('overridden-namespace', function (RouteNode $node) {
                $node->namespace('\RouteTreeTests\Feature\Controllers');
                $node->get( 'TestController@get');
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
                    'path' => 'de/overridden-namespace',
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
                    'path' => 'en/overridden-namespace',
                    'title' => 'Overridden-namespace'
                ],
            ]

        ]);

    }


    public function test_auto_translated_path_and_title()
    {

        $this->routeTree->node('products', function(RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get( 'TestController@get');
            $node->child('product1', function (RouteNode $node) {
                $node->get( 'TestController@get');
            });
            $node->child('product2', function (RouteNode $node) {
                $node->get( 'TestController@get');
            });
        });

        $this->routeTree->node('contact', function(RouteNode $node) {
            $node->get( '\RouteTreeTests\Feature\Controllers\TestController@get');
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
                    'path' => 'de/produkte',
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
                    'path' => 'en/products',
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
                    'path' => 'de/produkte/produkt_1',
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
                    'path' => 'en/products/product_1',
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
                    'path' => 'de/produkte/produkt_2',
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
                    'path' => 'en/products/product_2',
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
                    'path' => 'de/kontakt',
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
                    'path' => 'en/contact',
                    'title' => 'Contact us'
                ],
            ]

        ]);
    }

    public function test_custom_title()
    {
        $this->routeTree->node('page', function(RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get( 'TestController@get');
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
                    'path' => 'de/page',
                    'title' => 'Custom Title'
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
                    'path' => 'en/page',
                    'title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_custom_title_per_language()
    {

        $this->routeTree->node('page', function(RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get( 'TestController@get');
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
                    'path' => 'de/page',
                    'title' => 'Benutzerdefinierter Titel'
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
                    'path' => 'en/page',
                    'title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_custom_title_via_closure()
    {

        $this->routeTree->node('page', function(RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get( 'TestController@get');
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
                    'path' => 'de/page',
                    'title' => 'Benutzerdefinierter Titel'
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
                    'path' => 'en/page',
                    'title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_node_with_parameter_and_where()
    {
        $this->routeTree->node('my-node', function (RouteNode $node) {
            $node->segment('{foobar}');
            $node->where('foobar', '[0-9]+');
            $node->get(function() {
                return 'success';
            });
            $node->post(function() {
                return 'success';
            });
            $node->patch(function() {
                return 'success';
            })->where('foobar','[5-9]+');
        });

        $this->routeTree->generateAllRoutes();

        $this->get('de/123')->assertStatus(200)->assertSee('success');
        $this->get('de/abc')->assertStatus(404);
        $this->post('de/123')->assertStatus(200)->assertSee('success');
        $this->post('de/abc')->assertStatus(404);
        $this->patch('de/123')->assertStatus(405);
        $this->patch('de/567')->assertStatus(200)->assertSee('success');
        $this->patch('de/abc')->assertStatus(404);

    }

    public function test_node_with_middleware()
    {
        $this->routeTree->node('my-node', function (RouteNode $node) {
            $node->middleware('test1', ['is-inherited', 'is-skipped-by-post-action']);
            $node->middleware('test2', ['is-inherited-but-skipped-by-child'],true);
            $node->middleware('test3', ['is-not-inherited'],false);
            $node->get(function() {
                return 'success';
            });
            $node->post(function() {
                return 'success';
            })->skipMiddleware('test1');

            $node->child('my-child-node', function (RouteNode $node) {
                $node->middleware('test4', ['is-only-on-child']);
                $node->skipMiddleware('test2');
                $node->get(function() {
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

    public function test_node_with_all_features()
    {

        $this->routeTree->node('full-featured-node', function (RouteNode $node) {

            // Set path segment.
            $node->segment([
                'de' => 'RouteNode mit allen features.',
                'en' => 'RouteNode with all features.'
            ]);

            // Do not inherit segment to children.
            $node->inheritSegment(false);

            // Set middleware.
            $node->middleware('test1', ['param1']);

            // Set namespace.
            $node->namespace('\RouteTreeTests\Feature\Controllers');

            // Set actions.
            $node->get('TestController@get')
                // Set a middleware only for the get-action
                ->middleware('test2', ['param1', 'param2']);
            $node->post('TestController@post')
                //Skip the node-middleware 'test1'.
                ->skipMiddleware('test1');
            $node->put('TestController@put');
            $node->patch('TestController@patch');
            $node->delete('TestController@delete');
            $node->options('TestController@options');

            // Set various meta data.
            $node->title([
                'de' => 'Seitentitel',
                'en' => 'Page title'
            ]);
            $node->navTitle('Navigation title (falls back to title)');
            $node->h1Title(function () {
                return 'H1 title (falls back to title)';
            });
            $node->description([
                'de' => 'Diese RouteNode demonstriert alle Features.',
                'en' => 'This RouteNode demonstriert alle Features.'
            ]);

            // Set additional payload data.
            $node->payload->isMainNavItem = true;
            $node->payload->abstract = [
                'de' => 'RouteNode mit allen features.',
                'en' => 'RouteNode with all features.'
            ];

            // Add a (route parameter) child.
            $node->child('param-child', function (RouteNode $node) {

                // Set segment and route parameter regex.
                $node->segment('{param}');
                $node->where('param', '[0-9]+');

                // Do not generate node for german language.
                $node->exceptLocales(['de']);

                // Exclude this node from sitemap.
                $node->sitemap(false);
            });

            // Add a (permanently redirected) child.
            $node->child('redirect-child', function (RouteNode $node) {

                // Set segment.
                $node->segment('redirect-me');

                // Only generate node for german language.
                $node->onlyLocales(['de']);

                // Set action
                $node->permanentRedirect('full-featured-node'); // gets excluded from sitemap
            });

            // Add a child, that displays a view.
            $node->child('redirect-child', function (RouteNode $node) {

                // Set segment.
                $node->segment('displays-view');

                // Do not prefix this route with a locale,
                // making it single-language-only
                $node->noLocalePrefix();

                // Set action
                $node->view('test', ['foo' => 'bar']);
            });

            // Add a resource child.
            $node->child('resource-child', function (RouteNode $node) {

                // Create resource (only for 'index', 'show').
                $node->resource('resource', 'ResourceController')->only(['index', 'show']);

                // Set special title for 'show' action
                $node->resource->show->title(function (array $parameters, string $locale) {
                    $itemTitle = MyResourceModel::find($parameters['resource'])->title;
                    if ($locale === 'de') {
                        return "Meine Ressource: $itemTitle";
                    }
                    return "My resource: $itemTitle";

                });

                // Do not prefix this route with a locale,
                // making it single-language-only.
                $node->noLocalePrefix();

                // Set action
                $node->view('test', ['foo' => 'bar']);
            });

        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.get" => [
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
            "en.get" => [
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

        ]);
    }

}