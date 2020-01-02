<?php

namespace RouteTreeTests\Feature;

use Illuminate\Support\Facades\Event;
use RouteTreeTests\Feature\Traits\UsesTestRoutes;
use RouteTreeTests\TestCase;
use Webflorist\RouteTree\Events\LocaleChanged;
use Webflorist\RouteTree\Events\NodeNotFound;
use Webflorist\RouteTree\Events\Redirected;

class EventsTest extends TestCase
{
    use UsesTestRoutes;

    public function test_redirected_event()
    {
        Event::fake();

        $this->generateComplexTestRoutes($this->routeTree);

        $this->get('/', ['HTTP_ACCEPT_LANGUAGE' => 'de']);

        Event::assertDispatched(Redirected::class, function (Redirected $event) {
            $this->assertEquals('/', $event->fromUri);
            $this->assertEquals('de', $event->toUri);
            return true;
        });

    }

    public function test_locale_changed_event()
    {
        Event::fake();

        $this->generateComplexTestRoutes($this->routeTree);

        $this->get('de');

        Event::assertDispatched(LocaleChanged::class, function (LocaleChanged $event) {
            $this->assertEquals(null, $event->oldLocale);
            $this->assertEquals('de', $event->newLocale);
            return true;
        });

    }

    public function test_node_not_found_event()
    {
        Event::fake();

        $this->generateComplexTestRoutes($this->routeTree);

        $this->config->set('routetree.fallback_node', '');

        route_node('i-do-not-exist');

        Event::assertDispatched(NodeNotFound::class, function (NodeNotFound $event) {
            $this->assertEquals('i-do-not-exist', $event->nodeId);
            return true;
        });

    }

}
