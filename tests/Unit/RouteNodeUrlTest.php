<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;
use Webflorist\RouteTree\Domain\RouteNode;

class RouteNodeUrlTest extends TestCase
{

    public function test_node_url_simple()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            'http://localhost/de/page1',
            route_node_url('page1')
        );

    }

    public function test_node_url_relative()
    {
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            '/de/page1',
            route_node_url('page1', null, null, null, false)
        );

    }

    public function test_node_url_relative_via_config()
    {
        config()->set('routetree.absolute_urls', false);
        $this->generateTestRoutes('/de/page1');

        $this->assertEquals(
            '/de/page1',
            route_node_url('page1')
        );

    }

    public function test_node_url_complex_build()
    {
        $this->routeTree->node('page', function (RouteNode $node) {
            $node->resource('resource', '\RouteTreeTests\Feature\Controllers\TestController');
        });
        $this->routeTree->generateAllRoutes();

        $this->assertEquals(
            'http://localhost/en/page/my-slug/edit',
            route_node_url('page')
                ->locale('en')
                ->action('edit')
                ->absolute()
                ->parameters([
                    'resource' => 'my-slug'
                ])->__toString()
        );

        $this->assertEquals(
            'http://localhost/de/page/mein-slug/bearbeiten',
            route_node_url('page')
                ->locale('de')
                ->action('edit')
                ->absolute()
                ->parameters([
                    'resource' => 'mein-slug'
                ])->__toString()
        );

    }

    public function test_node_url_via_get_url_method_simple()
    {
        $this->routeTree->node('page', function (RouteNode $node) {
            $node->resource('resource', '\RouteTreeTests\Feature\Controllers\TestController');
        });
        $this->routeTree->generateAllRoutes();

        $this->assertEquals(
            'http://localhost/de/page',
            route_tree()->getNode('page')->getUrl()->__toString()
        );

    }

    public function test_node_url_via_get_url_method_complex()
    {
        $this->routeTree->node('page', function (RouteNode $node) {
            $node->resource('resource', '\RouteTreeTests\Feature\Controllers\TestController');
        });
        $this->routeTree->generateAllRoutes();

        $this->assertEquals(
            'http://localhost/en/page/my-slug/edit',
            route_tree()->getNode('page')->getUrl()
                ->locale('en')
                ->action('edit')
                ->absolute()
                ->parameters([
                    'resource' => 'my-slug'
                ])->__toString()
        );

    }

}
