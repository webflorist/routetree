<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;
use Webflorist\RouteTree\RouteNode;

class RouteGenerationTest extends TestCase
{

    public function test_root_node_with_all_methods()
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => ''
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
                    'title' => '',
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
                    'title' => '',
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

}