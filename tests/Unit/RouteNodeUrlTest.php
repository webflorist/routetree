<?php

namespace RouteTreeTests\Unit;

use RouteTreeTests\Feature\Models\TestModelTranslates;
use RouteTreeTests\Feature\Traits\UsesTestRoutes;
use RouteTreeTests\TestCase;
use Webflorist\RouteTree\LanguageMapping;
use Webflorist\RouteTree\RouteNode;

class RouteNodeUrlTest extends TestCase
{
    use UsesTestRoutes;

    public function test_node_url_simple()
    {
        $this->generateSimpleTestRoutes('/de/page1');

        $this->assertEquals(
            'http://localhost/de/page1',
            route_node('page1')->getUrl()
        );

    }

    public function test_node_url_relative()
    {
        $this->generateSimpleTestRoutes('/de/page1');

        $this->assertEquals(
            '/de/page1',
            route_node('page1')->getUrl()->absolute(false)
        );

    }

    public function test_node_url_relative_via_config()
    {
        config()->set('routetree.absolute_urls', false);
        $this->generateSimpleTestRoutes('/de/page1');

        $this->assertEquals(
            '/de/page1',
            route_node('page1')->getUrl()
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
            route_node('page')->getUrl()
                ->locale('en')
                ->action('edit')
                ->absolute()
                ->parameters([
                    'resource' => 'my-slug'
                ])->__toString()
        );

        $this->assertEquals(
            'http://localhost/de/page/mein-slug/bearbeiten',
            route_node('page')->getUrl()
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

    public function test_node_url_using_auto_parameters()
    {
        $this->routeTree->node('page', function (RouteNode $node) {
            $node->child('parameter1', function (RouteNode $node) {
                $node->parameter('parameter1');
                $node->child('parameter2', function (RouteNode $node) {
                    $node->parameter('parameter2');
                    $node->child('display-url', function (RouteNode $node) {
                        $node->get(function () {
                            return json_encode([(string)route_node_url()]);
                        });
                    });
                });
            });
        });
        $this->routeTree->generateAllRoutes();

        $this->assertJsonResponse(
            '/de/page/my-first-parameter/my-second-parameter/display-url',
            ['http://localhost/de/page/my-first-parameter/my-second-parameter/display-url']
        );

    }

    public function test_node_url_using_auto_parameters_and_translation_via_model()
    {
        $this->routeTree->node('page', function (RouteNode $node) {
            $node->child('parameter1', function (RouteNode $node) {
                $node->parameter('parameter_with_translated_values')->routeKeys(LanguageMapping::create([
                    'de' => [
                        'parameter1-wert1',
                        'parameter1-wert2'
                    ],
                    'en' => [
                        'parameter1-value1',
                        'parameter1-value2'
                    ]
                ]));
                $node->child('parameter2', function (RouteNode $node) {
                    $node->parameter('parameter_with_model')->model(TestModelTranslates::class);
                    $node->child('display-url', function (RouteNode $node) {
                        $node->get(function () {
                            return json_encode([(string)route_node_url()->locale('en')]);
                        });
                    });
                });
            });
        });
        $this->routeTree->generateAllRoutes();

        $this->assertJsonResponse(
            '/de/page/parameter1-wert2/test-model-wert1/display-url',
            ['http://localhost/en/page/parameter1-value2/test-model-value1/display-url']
        );

    }

}
