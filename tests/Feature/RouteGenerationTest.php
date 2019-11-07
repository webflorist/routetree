<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;
use Webflorist\RouteTree\RouteNode;

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

}