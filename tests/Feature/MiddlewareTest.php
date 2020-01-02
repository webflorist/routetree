<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\TestCase;
use Webflorist\RouteTree\LanguageMapping;
use Webflorist\RouteTree\RouteNode;

class MiddlewareTest extends TestCase
{

    protected function getDefaultAction()
    {
        return function () {
            return json_encode([
                'id' => route_tree()->getCurrentNode()->getId(),
                'path' => trim(\Request::getPathInfo(), '/'),
                'language' => \App::getLocale(),
            ]);
        };
    }

    public function test_default_language()
    {
        $this->config->set('app.locale', 'de');

        $this->routeTree->root(function (RouteNode $node) {
            $node->get($this->getDefaultAction());
        });
        $this->assertJsonResponse(
            '/',
            [
                "id" => "",
                "path" => "de",
                "language" => "de",
            ],
            true,
            ['HTTP_ACCEPT_LANGUAGE' => '']
        );
    }


    public function test_language_de()
    {
        $this->routeTree->root(function (RouteNode $node) {
            $node->get($this->getDefaultAction());
        });
        $this->assertJsonResponse('/de', [
            "id" => "",
            "path" => "de",
            "language" => "de",
        ]);
    }


    public function test_language_en()
    {

        $this->routeTree->root(function (RouteNode $node) {
            $node->get($this->getDefaultAction());
        });
        $this->assertJsonResponse('/en', [
            "id" => "",
            "path" => "en",
            "language" => "en",
        ]);
    }

    public function test_404()
    {
        $this->get('/foobar')->assertStatus(404);
    }

    public function test_auto_redirect_parent_german()
    {
        $this->routeTree->node('parent', function (RouteNode $node) {
            $node->get($this->getDefaultAction());
            $node->segment(LanguageMapping::create()
                ->set('de', 'eltern')
                ->set('en', 'parent'));
            $node->child('child', function (RouteNode $node) {
                $node->get($this->getDefaultAction());
            });
        });

        $this->assertJsonResponse('/eltern', [
            "id" => "parent",
            "path" => "de/eltern",
            "language" => "de",
        ], true);
    }

    public function test_auto_redirect_parent_english()
    {
        $this->routeTree->node('parent', function (RouteNode $node) {
            $node->get($this->getDefaultAction());
            $node->segment(LanguageMapping::create()
                ->set('de', 'eltern')
                ->set('en', 'parent'));
            $node->child('child', function (RouteNode $node) {
                $node->get($this->getDefaultAction());
            });
        });

        $this->assertJsonResponse('/parent', [
            "id" => "parent",
            "path" => "en/parent",
            "language" => "en",
        ], true);

    }

    public function test_auto_redirect_child_german()
    {
        $this->routeTree->node('parent', function (RouteNode $node) {
            $node->get($this->getDefaultAction());
            $node->segment(LanguageMapping::create()
                ->set('de', 'eltern')
                ->set('en', 'parent'));
            $node->child('child', function (RouteNode $node) {
                $node->get($this->getDefaultAction());
                $node->segment(LanguageMapping::create()
                    ->set('de', 'kind')
                    ->set('en', 'child'));
            });
        });

        $this->assertJsonResponse('/eltern/kind', [
            "id" => "parent.child",
            "path" => "de/eltern/kind",
            "language" => "de",
        ], true);

    }

    public function test_auto_redirect_child_english()
    {
        $this->routeTree->node('parent', function (RouteNode $node) {
            $node->get($this->getDefaultAction());
            $node->segment(LanguageMapping::create()
                ->set('de', 'eltern')
                ->set('en', 'parent'));
            $node->child('child', function (RouteNode $node) {
                $node->get($this->getDefaultAction());
                $node->segment(LanguageMapping::create()
                    ->set('de', 'kind')
                    ->set('en', 'child'));
            });
        });

        $this->assertJsonResponse('/parent/child', [
            "id" => "parent.child",
            "path" => "en/parent/child",
            "language" => "en",
        ], true);

    }

    public function test_auto_redirect_using_accept_language_header()
    {
        $this->routeTree->root(function (RouteNode $node) {
            $node->middleware('web');
            $node->get($this->getDefaultAction());
        });

        $this->assertJsonResponse(
            '',
            [
                "id" => "",
                "path" => "en",
                "language" => "en",
            ],
            true,
            ['HTTP_ACCEPT_LANGUAGE' => 'es,en']
        );

        // Once session is set, accept-language-header should have no effect anymore.
        $this->assertJsonResponse(
            '',
            [
                "id" => "",
                "path" => "en",
                "language" => "en",
            ],
            true,
            ['HTTP_ACCEPT_LANGUAGE' => 'de']
        );

    }


}
