<?php

namespace RouteTreeTests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Webflorist\RouteTree\RouteTree;
use Webflorist\RouteTree\RouteTreeServiceProvider;

/**
 * Class TestCase
 * @package PackageBlueprintTests
 */
class TestCase extends BaseTestCase
{
    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var RouteTree
     */
    protected $routeTree;

    /**
     * @var Router
     */
    protected $router;

    protected function getPackageProviders($app)
    {
        return [
            RouteTreeServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'RouteTree' => \Webflorist\RouteTree\Facades\RouteTree::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->router = $app[Router::class];
        $this->routeTree = $app[RouteTree::class];
        $this->config = $app['config'];

        // Set view config
        $this->config->set('view.paths', [
            dirname(__FILE__).'/Feature/Views'
        ]);

        $this->setConfig();
    }

    /**
     * Performs a test against all generated routes.
     */
    protected function assertRouteTree(array $expectedResult)
    {

        // Visit the root
        $this->get('');

        // Accumulate all routes
        $routes = [];
        foreach (\Route::getRoutes() as $route) {
            /** @var Route $route */
            $routeData = [
                'method' => str_replace('|HEAD', '', implode('|', $route->methods())),
                'uri' => $route->uri(),
                'action' => $route->getActionName(),
                'middleware' => $route->middleware()
            ];

            $response = $this->call($route->methods()[0], $route->uri());

            if ($response->isRedirection()) {
                $routeData['redirectTarget'] = $response->headers->get('Location');
                $routeData['statusCode'] = $response->getStatusCode();
            }
            else {
                $routeData['content'] = json_decode($response->baseResponse->getContent(), true);
            }

            $routes[$route->getName()] = $routeData;
        }

        // Sort expected and actual routes-array by key
        ksort($routes);
        ksort($expectedResult);

        // Assert, that expected and actual routes-array are equal.
        $this->assertEquals(
            $expectedResult,
            $routes
        );
    }

    protected function setConfig()
    {
        $this->config->set('routetree.locales',['en','de']);
    }


}