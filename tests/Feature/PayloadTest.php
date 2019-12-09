<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;
use Webflorist\RouteTree\LanguageMapping;
use Webflorist\RouteTree\RouteNode;

class PayloadTest extends TestCase
{

    public function test_node_with_payload()
    {
        $this->routeTree->node('payload', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->onlyLocales(['de']);
            $node->payload->myPayloadString = 'My payload string.';
            $node->payload->myPayloadArray = [
                'my' => 'payload',
                'ar' => 'ray'
            ];
            $node->payload->myPayloadInteger = 42;
            $node->payload->myPayloadBoolean = true;
        });

        $this->assertEquals(
            'My payload string.',
            $this->routeTree->getNode('payload')->payload->myPayloadString
        );

        $this->assertEquals(
            [
                'my' => 'payload',
                'ar' => 'ray'
            ],
            $this->routeTree->getNode('payload')->payload->myPayloadArray
        );

        $this->assertEquals(
            42,
            $this->routeTree->getNode('payload')->payload->myPayloadInteger
        );

        $this->assertEquals(
            true,
            $this->routeTree->getNode('payload')->payload->myPayloadBoolean
        );

    }

    public function test_node_with_translated_payload()
    {
        $this->routeTree->node('payload', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->payload->myPayloadString = LanguageMapping::create()
                ->set('en', 'My payload string.')
                ->set('de', 'Mein Payload String.');
            $node->payload->myPayloadArray = LanguageMapping::create()
                ->set('en', [
                    'my' => 'payload',
                    'ar' => 'ray'
                ])
                ->set('de', [
                    'mein' => 'payload',
                    'ar' => 'ray'
                ]);
            $node->payload->myPayloadInteger = LanguageMapping::create()
                ->set('en', 42)
                ->set('de', 43);
            $node->payload->myPayloadBoolean = LanguageMapping::create()
                ->set('en', true)
                ->set('de', false);
        });

        $this->routeTree->generateAllRoutes();

        $this->app->setLocale('en');

        $this->assertEquals(
            'My payload string.',
            $this->routeTree->getNode('payload')->payload->get('myPayloadString')
        );
        $this->assertEquals(
            [
                'my' => 'payload',
                'ar' => 'ray'
            ],
            $this->routeTree->getNode('payload')->payload->get('myPayloadArray')
        );
        $this->assertEquals(
            42,
            $this->routeTree->getNode('payload')->payload->get('myPayloadInteger')
        );
        $this->assertEquals(
            true,
            $this->routeTree->getNode('payload')->payload->get('myPayloadBoolean')
        );

        $this->app->setLocale('de');

        $this->assertEquals(
            'Mein Payload String.',
            $this->routeTree->getNode('payload')->payload->get('myPayloadString')
        );
        $this->assertEquals(
            [
                'mein' => 'payload',
                'ar' => 'ray'
            ],
            $this->routeTree->getNode('payload')->payload->get('myPayloadArray')
        );
        $this->assertEquals(
            43,
            $this->routeTree->getNode('payload')->payload->get('myPayloadInteger')
        );
        $this->assertEquals(
            false,
            $this->routeTree->getNode('payload')->payload->get('myPayloadBoolean')
        );


    }

    public function test_node_with_translated_closure_payload()
    {
        $this->routeTree->node('payload', function (RouteNode $node) {
            $node->get('\RouteTreeTests\Feature\Controllers\TestController@get');
            $node->payload->myPayloadClosure = function (array $parameters, string $locale) {
                return $locale === 'de' ? 'Meine Payload Closure.' : 'My payload closure.';
            };
        });

        $this->routeTree->generateAllRoutes();

        $this->app->setLocale('en');

        $this->assertEquals(
            'My payload closure.',
            $this->routeTree->getNode('payload')->payload->get('myPayloadClosure')
        );

        $this->app->setLocale('de');

        $this->assertEquals(
            'Meine Payload Closure.',
            $this->routeTree->getNode('payload')->payload->get('myPayloadClosure')
        );
    }

    public function test_title()
    {
        $this->routeTree->node('page', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
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
                    'locale' => 'de',
                    'path' => 'de/page',
                    'title' => 'Custom Title',
                    'navTitle' => 'Custom Title',
                    'h1Title' => 'Custom Title'
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
                    'locale' => 'en',
                    'path' => 'en/page',
                    'title' => 'Custom Title',
                    'navTitle' => 'Custom Title',
                    'h1Title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_title_per_language()
    {

        $this->routeTree->node('page', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
            $node->payload->title = LanguageMapping::create()
                ->set('de', 'Benutzerdefinierter Titel')
                ->set('en', 'Custom Title');
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
                    'locale' => 'de',
                    'path' => 'de/page',
                    'title' => 'Benutzerdefinierter Titel',
                    'navTitle' => 'Benutzerdefinierter Titel',
                    'h1Title' => 'Benutzerdefinierter Titel'
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
                    'locale' => 'en',
                    'path' => 'en/page',
                    'title' => 'Custom Title',
                    'navTitle' => 'Custom Title',
                    'h1Title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_title_via_closure()
    {

        $this->routeTree->node('page', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@get');
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
                    'locale' => 'de',
                    'path' => 'de/page',
                    'title' => 'Benutzerdefinierter Titel',
                    'navTitle' => 'Benutzerdefinierter Titel',
                    'h1Title' => 'Benutzerdefinierter Titel'
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
                    'locale' => 'en',
                    'path' => 'en/page',
                    'title' => 'Custom Title',
                    'navTitle' => 'Custom Title',
                    'h1Title' => 'Custom Title'
                ],
            ]
        ]);
    }

    public function test_titles_per_language_and_action()
    {

        $this->routeTree->node('comment', function (RouteNode $node) {
            $node->namespace('\RouteTreeTests\Feature\Controllers');
            $node->get('TestController@index', 'index');
            $node->get('TestController@create', 'create')->segment('create');

            $node->payload->title = LanguageMapping::create()
                ->set('de', 'Kommentare')
                ->set('en', 'Comments');

            $node->getAction('index')->payload->h1Title =
                LanguageMapping::create()
                    ->set('de', 'Liste von Kommentaren')
                    ->set('en', 'List of comments');

            $node->getAction('create')->payload->title(
                LanguageMapping::create()
                    ->set('de', 'Neuen Kommentar erstellen')
                    ->set('en', 'Add new comment')
            );

            $node->getAction('create')->payload->navTitle(
                LanguageMapping::create()
                    ->set('de', 'Kommentar erstellen')
                    ->set('en', 'Create comment')
            );
        });

        $this->routeTree->generateAllRoutes();

        $this->assertRouteTree([
            "de.comment.index" => [
                "method" => "GET",
                "uri" => "de/comment",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'comment',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/comment',
                    'title' => 'Kommentare',
                    'navTitle' => 'Kommentare',
                    'h1Title' => 'Liste von Kommentaren'
                ],
            ],
            "en.comment.index" => [
                "method" => "GET",
                "uri" => "en/comment",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@index',
                "middleware" => [],
                "content" => [
                    'id' => 'comment',
                    'controller' => 'test',
                    'function' => 'index',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/comment',
                    'title' => 'Comments',
                    'navTitle' => 'Comments',
                    'h1Title' => 'List of comments'
                ],
            ],
            "de.comment.create" => [
                "method" => "GET",
                "uri" => "de/comment/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'comment',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'de',
                    'path' => 'de/comment/create',
                    'title' => 'Neuen Kommentar erstellen',
                    'navTitle' => 'Kommentar erstellen',
                    'h1Title' => 'Neuen Kommentar erstellen'
                ],
            ],
            "en.comment.create" => [
                "method" => "GET",
                "uri" => "en/comment/create",
                "action" => '\RouteTreeTests\Feature\Controllers\TestController@create',
                "middleware" => [],
                "content" => [
                    'id' => 'comment',
                    'controller' => 'test',
                    'function' => 'create',
                    'method' => 'GET',
                    'locale' => 'en',
                    'path' => 'en/comment/create',
                    'title' => 'Add new comment',
                    'navTitle' => 'Create comment',
                    'h1Title' => 'Add new comment'
                ],
            ]
        ]);

    }


}