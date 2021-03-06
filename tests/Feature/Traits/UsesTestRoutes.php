<?php

namespace RouteTreeTests\Feature\Traits;

use Carbon\Carbon;
use RouteTreeTests\Feature\Models\BlogArticle;
use RouteTreeTests\Feature\Models\BlogCategory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webflorist\RouteTree\LanguageMapping;
use Webflorist\RouteTree\RouteNode;
use Webflorist\RouteTree\RouteTree;

trait UsesTestRoutes
{

    protected function generateSimpleTestRoutes($visitUri = '')
    {

        route_tree()->root(function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');

            $node->child('page1', function (RouteNode $node) {
                $node->get('TestController@get');

                $node->child('page1-1', function (RouteNode $node) {
                    $node->get('TestController@get');
                });

            });
        });

        route_tree()->generateAllRoutes();

        // Visit the uri.
        try {
            json_decode($this->get($visitUri)->baseResponse->getContent(), true);
        } catch (NotFoundHttpException $exception) {
            throw $exception;
        }

    }

    public static function generateComplexTestRoutes(RouteTree $routeTree): void
    {
        $routeTree->root(function (RouteNode $node) {
            $node->middleware('web');
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');

            $node->payload->translatedPayload = LanguageMapping::create()
                ->set('en', 'Translated Payload')
                ->set('de', 'Übersetzter Payload');
            $node->payload->set('booleanPayload', true);

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
                    $node->parameter('parameter-with-translated-values')->routeKeys(LanguageMapping::create()
                        ->set('de', [
                            'parameter-array-wert1', 'parameter-array-wert2'
                        ])
                        ->set('en', [
                            'parameter-array-value1', 'parameter-array-value2'
                        ])
                    );
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

        $routeTree->generateAllRoutes();
    }

    protected function assertComplexTestRouteTree(): void
    {
        $this->assertRouteTree(array(
            'de.auth.auth-child.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/auth/auth-child',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'auth' => 'auth',
                        'web' => 'web'
                    ),
                    'statusCode' => 500,
                ),
            'de.auth.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/auth',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'auth' => 'auth',
                        'web' => 'web'
                    ),
                    'statusCode' => 500,
                ),
            'de.blog-using-parameters.category.article.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/blog-using-parameters/{category}/{article}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-parameters.category.article',
                            'method' => 'GET',
                            'path' => 'de/blog-using-parameters/{category}/{article}',
                            'locale' => 'de',
                            'title' => 'Article',
                            'navTitle' => 'Article',
                            'h1Title' => 'Article',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.blog-using-parameters.category.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/blog-using-parameters/{category}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-parameters.category',
                            'method' => 'GET',
                            'path' => 'de/blog-using-parameters/{category}',
                            'locale' => 'de',
                            'title' => 'Category',
                            'navTitle' => 'Category',
                            'h1Title' => 'Category',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.blog-using-resources.articles.index' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/blog-using-resources/{category}/articles',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@index',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources.articles',
                            'method' => 'GET',
                            'path' => 'de/blog-using-resources/{category}/articles',
                            'locale' => 'de',
                            'title' => 'Articles',
                            'navTitle' => 'Articles',
                            'h1Title' => 'Articles',
                            'controller' => 'test',
                            'function' => 'index',
                        ),
                ),
            'de.blog-using-resources.articles.print.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/blog-using-resources/articles/{article}/print',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@print',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources.articles.print',
                            'method' => 'GET',
                            'path' => 'de/blog-using-resources/articles/{article}/print',
                            'locale' => 'de',
                            'title' => 'Print',
                            'navTitle' => 'Print',
                            'h1Title' => 'Print',
                            'controller' => 'test',
                            'function' => 'print',
                        ),
                ),
            'de.blog-using-resources.articles.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/blog-using-resources/{category}/articles/{article}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@show',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources.articles',
                            'method' => 'GET',
                            'path' => 'de/blog-using-resources/{category}/articles/{article}',
                            'locale' => 'de',
                            'title' => 'Articles: {article}',
                            'navTitle' => '{article}',
                            'h1Title' => 'Articles: {article}',
                            'controller' => 'test',
                            'function' => 'show',
                        ),
                ),
            'de.blog-using-resources.index' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/blog-using-resources',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@index',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources',
                            'method' => 'GET',
                            'path' => 'de/blog-using-resources',
                            'locale' => 'de',
                            'title' => 'Blog-using-resources',
                            'navTitle' => 'Blog-using-resources',
                            'h1Title' => 'Blog-using-resources',
                            'controller' => 'test',
                            'function' => 'index',
                        ),
                ),
            'de.blog-using-resources.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/blog-using-resources/{category}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@show',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources',
                            'method' => 'GET',
                            'path' => 'de/blog-using-resources/{category}',
                            'locale' => 'de',
                            'title' => 'Blog-using-resources: {category}',
                            'navTitle' => '{category}',
                            'h1Title' => 'Blog-using-resources: {category}',
                            'controller' => 'test',
                            'function' => 'show',
                        ),
                ),
            'de.excluded.excluded-child.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/excluded/excluded-child',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'excluded.excluded-child',
                            'method' => 'GET',
                            'path' => 'de/excluded/excluded-child',
                            'locale' => 'de',
                            'title' => 'Excluded-child',
                            'navTitle' => 'Excluded-child',
                            'h1Title' => 'Excluded-child',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.excluded.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/excluded',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'excluded',
                            'method' => 'GET',
                            'path' => 'de/excluded',
                            'locale' => 'de',
                            'title' => 'Excluded',
                            'navTitle' => 'Excluded',
                            'h1Title' => 'Excluded',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.excluded.non-excluded-child.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/excluded/non-excluded-child',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'excluded.non-excluded-child',
                            'method' => 'GET',
                            'path' => 'de/excluded/non-excluded-child',
                            'locale' => 'de',
                            'title' => 'Non-excluded-child',
                            'navTitle' => 'Non-excluded-child',
                            'h1Title' => 'Non-excluded-child',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => '',
                            'method' => 'GET',
                            'path' => 'de',
                            'locale' => 'de',
                            'title' => 'Startseite',
                            'navTitle' => 'Startseite',
                            'h1Title' => 'Startseite',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.parameter-with-translated-values.parameter-with-translated-values.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/parameter-with-translated-values/{parameter-with-translated-values}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'parameter-with-translated-values.parameter-with-translated-values',
                            'method' => 'GET',
                            'path' => 'de/parameter-with-translated-values/{parameter-with-translated-values}',
                            'locale' => 'de',
                            'title' => 'Parameter-with-translated-values',
                            'navTitle' => 'Parameter-with-translated-values',
                            'h1Title' => 'Parameter-with-translated-values',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.parameter-with-values.parameter-with-values.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/parameter-with-values/{parameter-with-values}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'parameter-with-values.parameter-with-values',
                            'method' => 'GET',
                            'path' => 'de/parameter-with-values/{parameter-with-values}',
                            'locale' => 'de',
                            'title' => 'Parameter-with-values',
                            'navTitle' => 'Parameter-with-values',
                            'h1Title' => 'Parameter-with-values',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.parameter.parameter.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/parameter/{parameter}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'parameter.parameter',
                            'method' => 'GET',
                            'path' => 'de/parameter/{parameter}',
                            'locale' => 'de',
                            'title' => 'Parameter',
                            'navTitle' => 'Parameter',
                            'h1Title' => 'Parameter',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'de.permanent-redirect.get' =>
                array(
                    'method' => 'GET|POST|PUT|PATCH|DELETE|OPTIONS',
                    'uri' => 'de/permanent-redirect',
                    'action' => '\\Illuminate\\Routing\\RedirectController',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'redirectTarget' => '/de/excluded',
                    'statusCode' => 301,
                ),
            'de.redirect.get' =>
                array(
                    'method' => 'GET|POST|PUT|PATCH|DELETE|OPTIONS',
                    'uri' => 'de/redirect',
                    'action' => '\\Illuminate\\Routing\\RedirectController',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'redirectTarget' => '/de/excluded',
                    'statusCode' => 302,
                ),
            'de.resource.create' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/resource/erstellen',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@create',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'GET',
                            'path' => 'de/resource/erstellen',
                            'locale' => 'de',
                            'title' => 'Resource erstellen',
                            'navTitle' => 'Erstellen',
                            'h1Title' => 'Resource erstellen',
                            'controller' => 'test',
                            'function' => 'create',
                        ),
                ),
            'de.resource.destroy' =>
                array(
                    'method' => 'DELETE',
                    'uri' => 'de/resource/{resource}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@destroy',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'DELETE',
                            'path' => 'de/resource/{resource}',
                            'locale' => 'de',
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'h1Title' => 'Resource',
                            'controller' => 'test',
                            'function' => 'destroy',
                        ),
                ),
            'de.resource.edit' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/resource/{resource}/bearbeiten',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@edit',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'GET',
                            'path' => 'de/resource/{resource}/bearbeiten',
                            'locale' => 'de',
                            'title' => 'Resource bearbeiten: {resource}',
                            'navTitle' => 'Bearbeiten',
                            'h1Title' => 'Resource bearbeiten: {resource}',
                            'controller' => 'test',
                            'function' => 'edit',
                        ),
                ),
            'de.resource.index' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/resource',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@index',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'GET',
                            'path' => 'de/resource',
                            'locale' => 'de',
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'h1Title' => 'Resource',
                            'controller' => 'test',
                            'function' => 'index',
                        ),
                ),
            'de.resource.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'de/resource/{resource}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@show',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'GET',
                            'path' => 'de/resource/{resource}',
                            'locale' => 'de',
                            'title' => 'Resource: {resource}',
                            'navTitle' => '{resource}',
                            'h1Title' => 'Resource: {resource}',
                            'controller' => 'test',
                            'function' => 'show',
                        ),
                ),
            'de.resource.store' =>
                array(
                    'method' => 'POST',
                    'uri' => 'de/resource',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@store',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'POST',
                            'path' => 'de/resource',
                            'locale' => 'de',
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'h1Title' => 'Resource',
                            'controller' => 'test',
                            'function' => 'store',
                        ),
                ),
            'de.resource.update' =>
                array(
                    'method' => 'PUT',
                    'uri' => 'de/resource/{resource}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@update',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'PUT',
                            'path' => 'de/resource/{resource}',
                            'locale' => 'de',
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'h1Title' => 'Resource',
                            'controller' => 'test',
                            'function' => 'update',
                        ),
                ),
            'en.auth.auth-child.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/auth/auth-child',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'auth' => 'auth',
                        'web' => 'web'
                    ),
                    'statusCode' => 500,
                ),
            'en.auth.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/auth',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'auth' => 'auth',
                        'web' => 'web'
                    ),
                    'statusCode' => 500,
                ),
            'en.blog-using-parameters.category.article.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/blog-using-parameters/{category}/{article}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-parameters.category.article',
                            'method' => 'GET',
                            'path' => 'en/blog-using-parameters/{category}/{article}',
                            'locale' => 'en',
                            'title' => 'Article',
                            'navTitle' => 'Article',
                            'h1Title' => 'Article',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.blog-using-parameters.category.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/blog-using-parameters/{category}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-parameters.category',
                            'method' => 'GET',
                            'path' => 'en/blog-using-parameters/{category}',
                            'locale' => 'en',
                            'title' => 'Category',
                            'navTitle' => 'Category',
                            'h1Title' => 'Category',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.blog-using-resources.articles.index' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/blog-using-resources/{category}/articles',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@index',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources.articles',
                            'method' => 'GET',
                            'path' => 'en/blog-using-resources/{category}/articles',
                            'locale' => 'en',
                            'title' => 'Articles',
                            'navTitle' => 'Articles',
                            'h1Title' => 'Articles',
                            'controller' => 'test',
                            'function' => 'index',
                        ),
                ),
            'en.blog-using-resources.articles.print.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/blog-using-resources/articles/{article}/print',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@print',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources.articles.print',
                            'method' => 'GET',
                            'path' => 'en/blog-using-resources/articles/{article}/print',
                            'locale' => 'en',
                            'title' => 'Print',
                            'navTitle' => 'Print',
                            'h1Title' => 'Print',
                            'controller' => 'test',
                            'function' => 'print',
                        ),
                ),
            'en.blog-using-resources.articles.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/blog-using-resources/{category}/articles/{article}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@show',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources.articles',
                            'method' => 'GET',
                            'path' => 'en/blog-using-resources/{category}/articles/{article}',
                            'locale' => 'en',
                            'title' => 'Articles: {article}',
                            'navTitle' => '{article}',
                            'h1Title' => 'Articles: {article}',
                            'controller' => 'test',
                            'function' => 'show',
                        ),
                ),
            'en.blog-using-resources.index' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/blog-using-resources',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@index',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources',
                            'method' => 'GET',
                            'path' => 'en/blog-using-resources',
                            'locale' => 'en',
                            'title' => 'Blog-using-resources',
                            'navTitle' => 'Blog-using-resources',
                            'h1Title' => 'Blog-using-resources',
                            'controller' => 'test',
                            'function' => 'index',
                        ),
                ),
            'en.blog-using-resources.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/blog-using-resources/{category}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@show',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'blog-using-resources',
                            'method' => 'GET',
                            'path' => 'en/blog-using-resources/{category}',
                            'locale' => 'en',
                            'title' => 'Blog-using-resources: {category}',
                            'navTitle' => '{category}',
                            'h1Title' => 'Blog-using-resources: {category}',
                            'controller' => 'test',
                            'function' => 'show',
                        ),
                ),
            'en.excluded.excluded-child.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/excluded/excluded-child',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'excluded.excluded-child',
                            'method' => 'GET',
                            'path' => 'en/excluded/excluded-child',
                            'locale' => 'en',
                            'title' => 'Excluded-child',
                            'navTitle' => 'Excluded-child',
                            'h1Title' => 'Excluded-child',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.excluded.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/excluded',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'excluded',
                            'method' => 'GET',
                            'path' => 'en/excluded',
                            'locale' => 'en',
                            'title' => 'Excluded',
                            'navTitle' => 'Excluded',
                            'h1Title' => 'Excluded',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.excluded.non-excluded-child.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/excluded/non-excluded-child',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'excluded.non-excluded-child',
                            'method' => 'GET',
                            'path' => 'en/excluded/non-excluded-child',
                            'locale' => 'en',
                            'title' => 'Non-excluded-child',
                            'navTitle' => 'Non-excluded-child',
                            'h1Title' => 'Non-excluded-child',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => '',
                            'method' => 'GET',
                            'path' => 'en',
                            'locale' => 'en',
                            'title' => 'Startpage',
                            'navTitle' => 'Startpage',
                            'h1Title' => 'Startpage',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.parameter-with-translated-values.parameter-with-translated-values.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/parameter-with-translated-values/{parameter-with-translated-values}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'parameter-with-translated-values.parameter-with-translated-values',
                            'method' => 'GET',
                            'path' => 'en/parameter-with-translated-values/{parameter-with-translated-values}',
                            'locale' => 'en',
                            'title' => 'Parameter-with-translated-values',
                            'navTitle' => 'Parameter-with-translated-values',
                            'h1Title' => 'Parameter-with-translated-values',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.parameter-with-values.parameter-with-values.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/parameter-with-values/{parameter-with-values}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'parameter-with-values.parameter-with-values',
                            'method' => 'GET',
                            'path' => 'en/parameter-with-values/{parameter-with-values}',
                            'locale' => 'en',
                            'title' => 'Parameter-with-values',
                            'navTitle' => 'Parameter-with-values',
                            'h1Title' => 'Parameter-with-values',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.parameter.parameter.get' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/parameter/{parameter}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@get',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'parameter.parameter',
                            'method' => 'GET',
                            'path' => 'en/parameter/{parameter}',
                            'locale' => 'en',
                            'title' => 'Parameter',
                            'navTitle' => 'Parameter',
                            'h1Title' => 'Parameter',
                            'controller' => 'test',
                            'function' => 'get',
                        ),
                ),
            'en.permanent-redirect.get' =>
                array(
                    'method' => 'GET|POST|PUT|PATCH|DELETE|OPTIONS',
                    'uri' => 'en/permanent-redirect',
                    'action' => '\\Illuminate\\Routing\\RedirectController',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'redirectTarget' => '/en/excluded',
                    'statusCode' => 301,
                ),
            'en.redirect.get' =>
                array(
                    'method' => 'GET|POST|PUT|PATCH|DELETE|OPTIONS',
                    'uri' => 'en/redirect',
                    'action' => '\\Illuminate\\Routing\\RedirectController',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'redirectTarget' => '/en/excluded',
                    'statusCode' => 302,
                ),
            'en.resource.create' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/resource/create',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@create',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'GET',
                            'path' => 'en/resource/create',
                            'locale' => 'en',
                            'title' => 'Create Resource',
                            'navTitle' => 'Create',
                            'h1Title' => 'Create Resource',
                            'controller' => 'test',
                            'function' => 'create',
                        ),
                ),
            'en.resource.destroy' =>
                array(
                    'method' => 'DELETE',
                    'uri' => 'en/resource/{resource}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@destroy',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'DELETE',
                            'path' => 'en/resource/{resource}',
                            'locale' => 'en',
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'h1Title' => 'Resource',
                            'controller' => 'test',
                            'function' => 'destroy',
                        ),
                ),
            'en.resource.edit' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/resource/{resource}/edit',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@edit',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'GET',
                            'path' => 'en/resource/{resource}/edit',
                            'locale' => 'en',
                            'title' => 'Edit Resource: {resource}',
                            'navTitle' => 'Edit',
                            'h1Title' => 'Edit Resource: {resource}',
                            'controller' => 'test',
                            'function' => 'edit',
                        ),
                ),
            'en.resource.index' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/resource',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@index',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'GET',
                            'path' => 'en/resource',
                            'locale' => 'en',
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'h1Title' => 'Resource',
                            'controller' => 'test',
                            'function' => 'index',
                        ),
                ),
            'en.resource.show' =>
                array(
                    'method' => 'GET',
                    'uri' => 'en/resource/{resource}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@show',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'GET',
                            'path' => 'en/resource/{resource}',
                            'locale' => 'en',
                            'title' => 'Resource: {resource}',
                            'navTitle' => '{resource}',
                            'h1Title' => 'Resource: {resource}',
                            'controller' => 'test',
                            'function' => 'show',
                        ),
                ),
            'en.resource.store' =>
                array(
                    'method' => 'POST',
                    'uri' => 'en/resource',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@store',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'POST',
                            'path' => 'en/resource',
                            'locale' => 'en',
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'h1Title' => 'Resource',
                            'controller' => 'test',
                            'function' => 'store',
                        ),
                ),
            'en.resource.update' =>
                array(
                    'method' => 'PUT',
                    'uri' => 'en/resource/{resource}',
                    'action' => '\\RouteTreeTests\\Feature\\Controllers\\TestController@update',
                    'middleware' => array(
                        'web' => 'web'
                    ),
                    'content' =>
                        array(
                            'id' => 'resource',
                            'method' => 'PUT',
                            'path' => 'en/resource/{resource}',
                            'locale' => 'en',
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'h1Title' => 'Resource',
                            'controller' => 'test',
                            'function' => 'update',
                        ),
                ),
        ));
    }

    private function assertComplexRegisteredRoutes()
    {
        $this->assertRegisteredRoutes(array(
            0 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.get',
                    'attributes' =>
                        array(
                            'node' => '',
                            'action' => 'get',
                            'uri' => 'de',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Startseite',
                            'navTitle' => 'Startseite',
                            'payload' => Array(
                                'translatedPayload' => 'Übersetzter Payload',
                                'booleanPayload' => true
                            ),
                        ),
                ),
            1 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.auth.get',
                    'attributes' =>
                        array(
                            'node' => 'auth',
                            'action' => 'get',
                            'uri' => 'de/auth',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Auth',
                            'navTitle' => 'Auth',
                            'payload' => Array(),
                        ),
                ),
            2 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.auth.auth-child.get',
                    'attributes' =>
                        array(
                            'node' => 'auth.auth-child',
                            'action' => 'get',
                            'uri' => 'de/auth/auth-child',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Auth-child',
                            'navTitle' => 'Auth-child',
                            'payload' => Array(),
                        ),
                ),
            3 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-parameters.category.show:blumen',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category',
                            'action' => 'show',
                            'uri' => 'de/blog-using-parameters/blumen',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Artikel über Blumen',
                            'navTitle' => 'Artikel über Blumen',
                            'payload' => Array(),
                        ),
                ),
            4 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-parameters.category.show:baeume',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category',
                            'action' => 'show',
                            'uri' => 'de/blog-using-parameters/baeume',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Artikel über Bäume',
                            'navTitle' => 'Artikel über Bäume',
                            'payload' => Array(),
                        ),
                ),
            5 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-parameters.category.article.show:blumen,die-rose',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'de/blog-using-parameters/blumen/die-rose',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Die Rose - Blume im Wandel der Zeit',
                            'navTitle' => 'Die Rose - Blume im Wandel der Zeit',
                            'payload' => Array(),
                        ),
                ),
            6 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-parameters.category.article.show:blumen,die-tulpe',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'de/blog-using-parameters/blumen/die-tulpe',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                            'navTitle' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                            'payload' => Array(),
                        ),
                ),
            7 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-parameters.category.article.show:blumen,die-lilie',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'de/blog-using-parameters/blumen/die-lilie',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Sehet die Lilien!',
                            'navTitle' => 'Sehet die Lilien!',
                            'payload' => Array(),
                        ),
                ),
            8 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-parameters.category.article.show:baeume,die-laerche',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'de/blog-using-parameters/baeume/die-laerche',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Und jetzt... Die Lärche',
                            'navTitle' => 'Und jetzt... Die Lärche',
                            'payload' => Array(),
                        ),
                ),
            9 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-parameters.category.article.show:baeume,die-laerche',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'de/blog-using-parameters/baeume/die-laerche',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Und jetzt... Die Lärche',
                            'navTitle' => 'Und jetzt... Die Lärche',
                            'payload' => Array(),
                        ),
                ),
            10 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-parameters.category.article.show:baeume,die-kastanie',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'de/blog-using-parameters/baeume/die-kastanie',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Und jetzt... Der Kastanienbaum',
                            'navTitle' => 'Und jetzt... Der Kastanienbaum',
                            'payload' => Array(),
                        ),
                ),
            11 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.index',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources',
                            'action' => 'index',
                            'uri' => 'de/blog-using-resources',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Blog-using-resources',
                            'navTitle' => 'Blog-using-resources',
                            'payload' => Array(),
                        ),
                ),
            12 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.show:blumen',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources',
                            'action' => 'show',
                            'uri' => 'de/blog-using-resources/blumen',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Artikel über Blumen',
                            'navTitle' => 'Artikel über Blumen',
                            'payload' => Array(),
                        ),
                ),
            13 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.show:baeume',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources',
                            'action' => 'show',
                            'uri' => 'de/blog-using-resources/baeume',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Artikel über Bäume',
                            'navTitle' => 'Artikel über Bäume',
                            'payload' => Array(),
                        ),
                ),
            14 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.articles.index:blumen',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'index',
                            'uri' => 'de/blog-using-resources/blumen/articles',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles',
                            'navTitle' => 'Articles',
                            'payload' => Array(),
                        ),
                ),
            15 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.articles.index:baeume',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'index',
                            'uri' => 'de/blog-using-resources/baeume/articles',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles',
                            'navTitle' => 'Articles',
                            'payload' => Array(),
                        ),
                ),
            16 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.articles.show:blumen,die-rose',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'de/blog-using-resources/blumen/articles/die-rose',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Die Rose - Blume im Wandel der Zeit',
                            'navTitle' => 'Die Rose - Blume im Wandel der Zeit',
                            'payload' => Array(),
                        ),
                ),
            17 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.articles.show:blumen,die-tulpe',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'de/blog-using-resources/blumen/articles/die-tulpe',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                            'navTitle' => 'Dit Tulpe im weltgeschichtlichen Finanzsystem',
                            'payload' => Array(),
                        ),
                ),
            18 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.articles.show:blumen,die-lilie',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'de/blog-using-resources/blumen/articles/die-lilie',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Sehet die Lilien!',
                            'navTitle' => 'Sehet die Lilien!',
                            'payload' => Array(),
                        ),
                ),
            19 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.articles.show:baeume,die-laerche',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'de/blog-using-resources/baeume/articles/die-laerche',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Und jetzt... Die Lärche',
                            'navTitle' => 'Und jetzt... Die Lärche',
                            'payload' => Array(),
                        ),
                ),
            20 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.articles.show:baeume,die-laerche',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'de/blog-using-resources/baeume/articles/die-laerche',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Und jetzt... Die Lärche',
                            'navTitle' => 'Und jetzt... Die Lärche',
                            'payload' => Array(),
                        ),
                ),
            21 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.blog-using-resources.articles.show:baeume,die-kastanie',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'de/blog-using-resources/baeume/articles/die-kastanie',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Und jetzt... Der Kastanienbaum',
                            'navTitle' => 'Und jetzt... Der Kastanienbaum',
                            'payload' => Array(),
                        ),
                ),
            22 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.excluded.get',
                    'attributes' =>
                        array(
                            'node' => 'excluded',
                            'action' => 'get',
                            'uri' => 'de/excluded',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Excluded',
                            'navTitle' => 'Excluded',
                            'payload' => Array(),
                        ),
                ),
            23 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.excluded.excluded-child.get',
                    'attributes' =>
                        array(
                            'node' => 'excluded.excluded-child',
                            'action' => 'get',
                            'uri' => 'de/excluded/excluded-child',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Excluded-child',
                            'navTitle' => 'Excluded-child',
                            'payload' => Array(),
                        ),
                ),
            24 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.excluded.non-excluded-child.get',
                    'attributes' =>
                        array(
                            'node' => 'excluded.non-excluded-child',
                            'action' => 'get',
                            'uri' => 'de/excluded/non-excluded-child',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Non-excluded-child',
                            'navTitle' => 'Non-excluded-child',
                            'payload' => Array(),
                        ),
                ),
            25 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.parameter-with-translated-values.parameter-with-translated-values.get:parameter-array-wert1',
                    'attributes' =>
                        array(
                            'node' => 'parameter-with-translated-values.parameter-with-translated-values',
                            'action' => 'get',
                            'uri' => 'de/parameter-with-translated-values/parameter-array-wert1',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Parameter-with-translated-values',
                            'navTitle' => 'Parameter-with-translated-values',
                            'payload' => Array(),
                        ),
                ),
            26 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.parameter-with-translated-values.parameter-with-translated-values.get:parameter-array-wert2',
                    'attributes' =>
                        array(
                            'node' => 'parameter-with-translated-values.parameter-with-translated-values',
                            'action' => 'get',
                            'uri' => 'de/parameter-with-translated-values/parameter-array-wert2',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Parameter-with-translated-values',
                            'navTitle' => 'Parameter-with-translated-values',
                            'payload' => Array(),
                        ),
                ),
            27 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.parameter-with-values.parameter-with-values.get:parameter-array-value1',
                    'attributes' =>
                        array(
                            'node' => 'parameter-with-values.parameter-with-values',
                            'action' => 'get',
                            'uri' => 'de/parameter-with-values/parameter-array-value1',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Parameter-with-values',
                            'navTitle' => 'Parameter-with-values',
                            'payload' => Array(),
                        ),
                ),
            28 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.parameter-with-values.parameter-with-values.get:parameter-array-value2',
                    'attributes' =>
                        array(
                            'node' => 'parameter-with-values.parameter-with-values',
                            'action' => 'get',
                            'uri' => 'de/parameter-with-values/parameter-array-value2',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Parameter-with-values',
                            'navTitle' => 'Parameter-with-values',
                            'payload' => Array(),
                        ),
                ),
            29 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.permanent-redirect.get',
                    'attributes' =>
                        array(
                            'node' => 'permanent-redirect',
                            'action' => 'get',
                            'uri' => 'de/permanent-redirect',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                    2 => 'POST',
                                    3 => 'PUT',
                                    4 => 'PATCH',
                                    5 => 'DELETE',
                                    6 => 'OPTIONS',
                                ),
                            'title' => 'Permanent-redirect',
                            'navTitle' => 'Permanent-redirect',
                            'payload' => Array(),
                        ),
                ),
            30 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.redirect.get',
                    'attributes' =>
                        array(
                            'node' => 'redirect',
                            'action' => 'get',
                            'uri' => 'de/redirect',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                    2 => 'POST',
                                    3 => 'PUT',
                                    4 => 'PATCH',
                                    5 => 'DELETE',
                                    6 => 'OPTIONS',
                                ),
                            'title' => 'Redirect',
                            'navTitle' => 'Redirect',
                            'payload' => Array(),
                        ),
                ),
            31 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.resource.index',
                    'attributes' =>
                        array(
                            'node' => 'resource',
                            'action' => 'index',
                            'uri' => 'de/resource',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'payload' => Array(),
                        ),
                ),
            32 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.resource.store',
                    'attributes' =>
                        array(
                            'node' => 'resource',
                            'action' => 'store',
                            'uri' => 'de/resource',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'POST',
                                ),
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'payload' => Array(),
                        ),
                ),
            33 =>
                array(
                    'type' => 'routes',
                    'id' => 'de.resource.create',
                    'attributes' =>
                        array(
                            'node' => 'resource',
                            'action' => 'create',
                            'uri' => 'de/resource/erstellen',
                            'locale' => 'de',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Resource erstellen',
                            'navTitle' => 'Erstellen',
                            'payload' => Array(),
                        ),
                ),
            34 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.get',
                    'attributes' =>
                        array(
                            'node' => '',
                            'action' => 'get',
                            'uri' => 'en',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Startpage',
                            'navTitle' => 'Startpage',
                            'payload' => Array(
                                'translatedPayload' => 'Translated Payload',
                                'booleanPayload' => true
                            ),
                        ),
                ),
            35 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.auth.get',
                    'attributes' =>
                        array(
                            'node' => 'auth',
                            'action' => 'get',
                            'uri' => 'en/auth',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Auth',
                            'navTitle' => 'Auth',
                            'payload' => Array(),
                        ),
                ),
            36 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.auth.auth-child.get',
                    'attributes' =>
                        array(
                            'node' => 'auth.auth-child',
                            'action' => 'get',
                            'uri' => 'en/auth/auth-child',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Auth-child',
                            'navTitle' => 'Auth-child',
                            'payload' => Array(),
                        ),
                ),
            37 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-parameters.category.show:flowers',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category',
                            'action' => 'show',
                            'uri' => 'en/blog-using-parameters/flowers',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles about flowers',
                            'navTitle' => 'Articles about flowers',
                            'payload' => Array(),
                        ),
                ),
            38 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-parameters.category.show:trees',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category',
                            'action' => 'show',
                            'uri' => 'en/blog-using-parameters/trees',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles about trees',
                            'navTitle' => 'Articles about trees',
                            'payload' => Array(),
                        ),
                ),
            39 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-parameters.category.article.show:flowers,the-rose',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'en/blog-using-parameters/flowers/the-rose',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Article',
                            'navTitle' => 'Article',
                            'payload' => Array(),
                        ),
                ),
            40 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-parameters.category.article.show:flowers,the-tulip',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'en/blog-using-parameters/flowers/the-tulip',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Article',
                            'navTitle' => 'Article',
                            'payload' => Array(),
                        ),
                ),
            41 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-parameters.category.article.show:flowers,the-lily',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'en/blog-using-parameters/flowers/the-lily',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Article',
                            'navTitle' => 'Article',
                            'payload' => Array(),
                        ),
                ),
            42 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-parameters.category.article.show:trees,the-larch',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'en/blog-using-parameters/trees/the-larch',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Article',
                            'navTitle' => 'Article',
                            'payload' => Array(),
                        ),
                ),
            43 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-parameters.category.article.show:trees,the-larch',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'en/blog-using-parameters/trees/the-larch',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Article',
                            'navTitle' => 'Article',
                            'payload' => Array(),
                        ),
                ),
            44 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-parameters.category.article.show:trees,the-chestnut',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-parameters.category.article',
                            'action' => 'show',
                            'uri' => 'en/blog-using-parameters/trees/the-chestnut',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Article',
                            'navTitle' => 'Article',
                            'payload' => Array(),
                        ),
                ),
            45 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.index',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources',
                            'action' => 'index',
                            'uri' => 'en/blog-using-resources',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Blog-using-resources',
                            'navTitle' => 'Blog-using-resources',
                            'payload' => Array(),
                        ),
                ),
            46 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.show:flowers',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources',
                            'action' => 'show',
                            'uri' => 'en/blog-using-resources/flowers',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles about flowers',
                            'navTitle' => 'Articles about flowers',
                            'payload' => Array(),
                        ),
                ),
            47 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.show:trees',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources',
                            'action' => 'show',
                            'uri' => 'en/blog-using-resources/trees',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles about trees',
                            'navTitle' => 'Articles about trees',
                            'payload' => Array(),
                        ),
                ),
            48 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.articles.index:flowers',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'index',
                            'uri' => 'en/blog-using-resources/flowers/articles',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles',
                            'navTitle' => 'Articles',
                            'payload' => Array(),
                        ),
                ),
            49 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.articles.index:trees',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'index',
                            'uri' => 'en/blog-using-resources/trees/articles',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles',
                            'navTitle' => 'Articles',
                            'payload' => Array(),
                        ),
                ),
            50 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.articles.show:flowers,the-rose',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'en/blog-using-resources/flowers/articles/the-rose',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles: the-rose',
                            'navTitle' => 'the-rose',
                            'payload' => Array(),
                        ),
                ),
            51 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.articles.show:flowers,the-tulip',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'en/blog-using-resources/flowers/articles/the-tulip',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles: the-tulip',
                            'navTitle' => 'the-tulip',
                            'payload' => Array(),
                        ),
                ),
            52 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.articles.show:flowers,the-lily',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'en/blog-using-resources/flowers/articles/the-lily',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles: the-lily',
                            'navTitle' => 'the-lily',
                            'payload' => Array(),
                        ),
                ),
            53 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.articles.show:trees,the-larch',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'en/blog-using-resources/trees/articles/the-larch',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles: the-larch',
                            'navTitle' => 'the-larch',
                            'payload' => Array(),
                        ),
                ),
            54 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.articles.show:trees,the-larch',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'en/blog-using-resources/trees/articles/the-larch',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles: the-larch',
                            'navTitle' => 'the-larch',
                            'payload' => Array(),
                        ),
                ),
            55 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.blog-using-resources.articles.show:trees,the-chestnut',
                    'attributes' =>
                        array(
                            'node' => 'blog-using-resources.articles',
                            'action' => 'show',
                            'uri' => 'en/blog-using-resources/trees/articles/the-chestnut',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Articles: the-chestnut',
                            'navTitle' => 'the-chestnut',
                            'payload' => Array(),
                        ),
                ),
            56 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.excluded.get',
                    'attributes' =>
                        array(
                            'node' => 'excluded',
                            'action' => 'get',
                            'uri' => 'en/excluded',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Excluded',
                            'navTitle' => 'Excluded',
                            'payload' => Array(),
                        ),
                ),
            57 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.excluded.excluded-child.get',
                    'attributes' =>
                        array(
                            'node' => 'excluded.excluded-child',
                            'action' => 'get',
                            'uri' => 'en/excluded/excluded-child',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Excluded-child',
                            'navTitle' => 'Excluded-child',
                            'payload' => Array(),
                        ),
                ),
            58 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.excluded.non-excluded-child.get',
                    'attributes' =>
                        array(
                            'node' => 'excluded.non-excluded-child',
                            'action' => 'get',
                            'uri' => 'en/excluded/non-excluded-child',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Non-excluded-child',
                            'navTitle' => 'Non-excluded-child',
                            'payload' => Array(),
                        ),
                ),
            59 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.parameter-with-translated-values.parameter-with-translated-values.get:parameter-array-value1',
                    'attributes' =>
                        array(
                            'node' => 'parameter-with-translated-values.parameter-with-translated-values',
                            'action' => 'get',
                            'uri' => 'en/parameter-with-translated-values/parameter-array-value1',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Parameter-with-translated-values',
                            'navTitle' => 'Parameter-with-translated-values',
                            'payload' => Array(),
                        ),
                ),
            60 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.parameter-with-translated-values.parameter-with-translated-values.get:parameter-array-value2',
                    'attributes' =>
                        array(
                            'node' => 'parameter-with-translated-values.parameter-with-translated-values',
                            'action' => 'get',
                            'uri' => 'en/parameter-with-translated-values/parameter-array-value2',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Parameter-with-translated-values',
                            'navTitle' => 'Parameter-with-translated-values',
                            'payload' => Array(),
                        ),
                ),
            61 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.parameter-with-values.parameter-with-values.get:parameter-array-value1',
                    'attributes' =>
                        array(
                            'node' => 'parameter-with-values.parameter-with-values',
                            'action' => 'get',
                            'uri' => 'en/parameter-with-values/parameter-array-value1',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Parameter-with-values',
                            'navTitle' => 'Parameter-with-values',
                            'payload' => Array(),
                        ),
                ),
            62 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.parameter-with-values.parameter-with-values.get:parameter-array-value2',
                    'attributes' =>
                        array(
                            'node' => 'parameter-with-values.parameter-with-values',
                            'action' => 'get',
                            'uri' => 'en/parameter-with-values/parameter-array-value2',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Parameter-with-values',
                            'navTitle' => 'Parameter-with-values',
                            'payload' => Array(),
                        ),
                ),
            63 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.permanent-redirect.get',
                    'attributes' =>
                        array(
                            'node' => 'permanent-redirect',
                            'action' => 'get',
                            'uri' => 'en/permanent-redirect',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                    2 => 'POST',
                                    3 => 'PUT',
                                    4 => 'PATCH',
                                    5 => 'DELETE',
                                    6 => 'OPTIONS',
                                ),
                            'title' => 'Permanent-redirect',
                            'navTitle' => 'Permanent-redirect',
                            'payload' => Array(),
                        ),
                ),
            64 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.redirect.get',
                    'attributes' =>
                        array(
                            'node' => 'redirect',
                            'action' => 'get',
                            'uri' => 'en/redirect',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                    2 => 'POST',
                                    3 => 'PUT',
                                    4 => 'PATCH',
                                    5 => 'DELETE',
                                    6 => 'OPTIONS',
                                ),
                            'title' => 'Redirect',
                            'navTitle' => 'Redirect',
                            'payload' => Array(),
                        ),
                ),
            65 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.resource.index',
                    'attributes' =>
                        array(
                            'node' => 'resource',
                            'action' => 'index',
                            'uri' => 'en/resource',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'payload' => Array(),
                        ),
                ),
            66 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.resource.store',
                    'attributes' =>
                        array(
                            'node' => 'resource',
                            'action' => 'store',
                            'uri' => 'en/resource',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'POST',
                                ),
                            'title' => 'Resource',
                            'navTitle' => 'Resource',
                            'payload' => Array(),
                        ),
                ),
            67 =>
                array(
                    'type' => 'routes',
                    'id' => 'en.resource.create',
                    'attributes' =>
                        array(
                            'node' => 'resource',
                            'action' => 'create',
                            'uri' => 'en/resource/create',
                            'locale' => 'en',
                            'methods' =>
                                array(
                                    0 => 'GET',
                                    1 => 'HEAD',
                                ),
                            'title' => 'Create Resource',
                            'navTitle' => 'Create',
                            'payload' => Array(),
                        ),
                ),
        ));
    }

}