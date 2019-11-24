<?php

namespace Webflorist\RouteTree\Listeners;

use Illuminate\Console\Events\CommandFinished;

class CommandListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ProductPageViewEvent  $event
     * @return void
     */
    public function handle(CommandFinished $event)
    {
        if ($event->command === "route:cache" && $event->exitCode === 0) {
            dd(route_tree()->getRegisteredRoutes());
        }
    }
}
