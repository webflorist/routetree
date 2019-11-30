<?php

namespace RouteTreeTests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RouteTreeTests\Feature\Middleware\Test1Middleware;
use RouteTreeTests\Feature\Middleware\Test2Middleware;
use RouteTreeTests\Feature\Middleware\Test3Middleware;
use RouteTreeTests\Feature\Middleware\Test4Middleware;
use Webflorist\RouteTree\Http\Resources\RouteCollection;
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
            dirname(__FILE__) . '/Feature/Views'
        ]);

        $this->setConfig();

        // Register test-middleware.
        $this->router->aliasMiddleware('test1', Test1Middleware::class);
        $this->router->aliasMiddleware('test2', Test2Middleware::class);
        $this->router->aliasMiddleware('test3', Test3Middleware::class);
        $this->router->aliasMiddleware('test4', Test4Middleware::class);

        // Add Translations
        $app['translator']->addNamespace('RouteTreeTests', __DIR__ . "/Feature/lang");
    }

    protected function setUp(): void
    {
        $this->deleteCacheFiles();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->app->forgetInstance(RouteTree::class);
        parent::tearDown();
        $this->deleteCacheFiles();
    }


    /**
     * Performs a test against all generated routes.
     *
     * @param array $expectedResult
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
            } else if ($response->getStatusCode() == 500) {
                $routeData['statusCode'] = $response->getStatusCode();
            } else {
                $content = $response->baseResponse->getContent();
                if ($this->isJson()->evaluate($content, '', true)) {
                    $content = json_decode($content, true);
                }
                $routeData['content'] = $content;
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
        $this->config->set('app.locale', 'de');
        $this->config->set('routetree.locales', ['en', 'de']);
        $this->config->set('routetree.localization.base_folder', 'RouteTreeTests::pages');
    }

    protected function assertRegisteredRoutes(array $expectedRoutes)
    {
        //file_put_contents('test.txt',var_export(json_decode((new RouteCollection(route_tree()->getRegisteredRoutes(true)))->collection->toJson(),true),true));
        $this->assertEquals(
            $expectedRoutes,
            json_decode((new RouteCollection(route_tree()->getRegisteredRoutes(true)))->collection->toJson(), true)
        );
    }

    protected function assertJsonResponse(string $uri, array $expected, bool $followRedirects = false, array $headers = [])
    {
        $response = $this->get($uri, $headers);
        if ($followRedirects) {
            $response = $this->followRedirects($response);
        }
        $this->assertEquals(
            $expected,
            json_decode($response->baseResponse->getContent(), true)
        );

    }

    protected function deleteCacheFiles(): void
    {
        $cacheFiles = [
            __DIR__ . '/../vendor/orchestra/testbench-core/laravel/bootstrap/cache/routes.php',
            __DIR__ . '/../vendor/orchestra/testbench-core/laravel/bootstrap/cache/routetree.php'
        ];
        foreach ($cacheFiles as $cacheFile) {
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        }
    }


    public static function getRouteTestData(array $additionalData = []): string
    {
        if (route_tree()->getCurrentAction()->getName() === 'create') {
            //dd(route_tree()->getCurrentNode()->getTitle());
        }
        return json_encode(
            array_merge(
                [
                    'id' => route_tree()->getCurrentNode()->getId(),
                    'method' => \Request::getMethod(),
                    'path' => trim(\Request::getPathInfo(), '/'),
                    'locale' => app()->getLocale(),
                    'title' => route_tree()->getCurrentAction()->getTitle(),
                    'navTitle' => route_tree()->getCurrentAction()->getNavTitle(),
                    'h1Title' => route_tree()->getCurrentAction()->payload->get('h1Title') ?? route_tree()->getCurrentNode()->payload->get('h1Title') ?? route_tree()->getCurrentNode()->getTitle()
                ],
                $additionalData
            )
        );
    }


}