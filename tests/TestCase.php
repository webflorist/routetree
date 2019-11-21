<?php

namespace RouteTreeTests;

use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RouteTreeTests\Feature\Middleware\Test1Middleware;
use RouteTreeTests\Feature\Middleware\Test2Middleware;
use RouteTreeTests\Feature\Middleware\Test3Middleware;
use RouteTreeTests\Feature\Middleware\Test4Middleware;
use RouteTreeTests\Feature\Models\BlogArticle;
use RouteTreeTests\Feature\Models\BlogCategory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webflorist\RouteTree\Domain\RouteNode;
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

        // Register test-middleware.
        $this->router->aliasMiddleware('test1', Test1Middleware::class);
        $this->router->aliasMiddleware('test2', Test2Middleware::class);
        $this->router->aliasMiddleware('test3', Test3Middleware::class);
        $this->router->aliasMiddleware('test4', Test4Middleware::class);

        // Add Translations
        $app['translator']->addNamespace('RouteTreeTests', __DIR__ . "/Feature/lang");
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
            }
            else {
                $content = $response->baseResponse->getContent();
                if($this->isJson()->evaluate($content,'',true)) {
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
        $this->config->set('app.locale','de');
        $this->config->set('routetree.locales',['en','de']);
        $this->config->set('routetree.localization.base_folder','RouteTreeTests::pages');
    }


    protected function generateComplexTestRoutes(): void
    {
        $this->routeTree->root(function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->sitemap
                ->lastmod(Carbon::parse('2019-11-16T17:46:30.45+01:00'))
                ->changefreq('monthly')
                ->priority(1.0);
            $node->child('excluded', function (RouteNode $node) {
                $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                $node->sitemap
                    ->exclude();
                $node->child('excluded-child', function (RouteNode $node) {
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                });
                $node->child('non-excluded-child', function (RouteNode $node) {
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                    $node->sitemap
                        ->exclude(false);
                });
            });
            $node->child('redirect', function (RouteNode $node) {
                $node->redirect('excluded');
            });
            $node->child('permanent-redirect', function (RouteNode $node) {
                $node->permanentRedirect('excluded');
            });
            $node->child('parameter', function (RouteNode $node) {
                $node->child('parameter', function (RouteNode $node) {
                    $node->segment('{parameter}');
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                });
            });
            $node->child('parameter-with-values', function (RouteNode $node) {
                $node->child('parameter-with-values', function (RouteNode $node) {
                    $node->parameter('parameter-with-values')->routeKeys([
                        'parameter-array-value1', 'parameter-array-value2'
                    ]);
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                });
            });
            $node->child('parameter-with-translated-values', function (RouteNode $node) {
                $node->child('parameter-with-translated-values', function (RouteNode $node) {
                    $node->parameter('parameter-with-translated-values')->routeKeys([
                        'de' => [
                            'parameter-array-wert1', 'parameter-array-wert2'
                        ],
                        'en' => [
                            'parameter-array-value1', 'parameter-array-value2'
                        ]
                    ]);
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                });
            });
            $node->child('blog-using-parameters', function (RouteNode $node) {
                $node->child('category', function (RouteNode $node) {
                    $node->parameter('category')->model(BlogCategory::class);
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get')->name('show');
                    $node->child('article', function (RouteNode $node) {
                        $node->parameter('article')->model(BlogArticle::class);
                        $node->get('\RouteTreeTests\Feature\Controllers\TestController@get')->name('show');
                    });
                });
            });
            $node->child('resource', function (RouteNode $node) {
                $node->resource('resource', '\RouteTreeTests\Feature\Controllers\TestController');
            });
            $node->child('blog-using-resources', function (RouteNode $node) {
                $node->resource('category', '\RouteTreeTests\Feature\Controllers\TestController')
                    ->only(['index', 'show'])
                    ->model(BlogCategory::class)
                    ->child('articles', function (RouteNode $node) {
                        $node->resource('article', '\RouteTreeTests\Feature\Controllers\TestController')
                            ->only(['index', 'show'])
                            ->model(BlogArticle::class)
                            ->child('print', function (RouteNode $node) {
                                $node->get('\RouteTreeTests\Feature\Controllers\TestController@print');
                            });
                    });;
            });
            $node->child('auth', function (RouteNode $node) {
                $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                $node->middleware('auth');
                $node->child('auth-child', function (RouteNode $node) {
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                });
            });
        });

        $this->routeTree->generateAllRoutes();
    }


    protected function generateSimpleTestRoutes($visitUri='') {

        route_tree()->root(function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');

            $node->child('page1', function(RouteNode $node) {
                $node->get('TestController@get');

                $node->child('page1-1', function(RouteNode $node) {
                    $node->get('TestController@get');
                });

            });
        });

        route_tree()->generateAllRoutes();

        // Visit the uri.
        try {
            json_decode($this->get($visitUri)->baseResponse->getContent(), true);
        }
        catch(NotFoundHttpException $exception) {
            throw $exception;
        }

    }

    protected function assertJsonResponse(string $uri, array $expected, bool$followRedirects=false, array $headers = [])
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



}