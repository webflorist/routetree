<?php

namespace RouteTreeTests\Feature;

use Carbon\Carbon;
use RouteTreeTests\Feature\Models\TestModelTranslatable;
use RouteTreeTests\TestCase;
use Webflorist\RouteTree\Domain\RouteNode;

class ApiTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $this->config->set('routetree.api.enabled', true);
    }

    public function test_paths_index()
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
                    $node->parameter('parameter-with-values')->values([
                        'parameter-array-value1', 'parameter-array-value2'
                    ]);
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                });
            });
            $node->child('parameter-with-translated-values', function (RouteNode $node) {
                $node->child('parameter-with-translated-values', function (RouteNode $node) {
                    $node->parameter('parameter-with-translated-values')->values([
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
            $node->child('parameter-with-model', function (RouteNode $node) {
                $node->child('parameter-with-model', function (RouteNode $node) {
                    $node->parameter('parameter-with-model')->model(TestModelTranslatable::class);
                    $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
                });
            });
            $node->child('resource', function (RouteNode $node) {
                $node->resource('resource', '\RouteTreeTests\Feature\Controllers\TestController');
            });
            $node->child('resource-with-model', function (RouteNode $node) {
                $node->resource('resource-with-model', '\RouteTreeTests\Feature\Controllers\TestController')->model(TestModelTranslatable::class);
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

        dd($this->get('api/routetree/paths')->decodeResponseJson());
    }


}