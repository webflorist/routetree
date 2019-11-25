<?php

namespace RouteTreeTests\Feature\Traits;

use RouteTreeTests\Feature\Providers\RouteServiceProvider;
use Webflorist\RouteTree\RouteTreeServiceProvider;

trait UsesCachedRoutes
{

    protected function getPackageProviders($app)
    {
        return [
            RouteTreeServiceProvider::class,
            RouteServiceProvider::class
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('routetree:route-cache')->assertExitCode('0');
        $this->refreshApplication();
    }

}