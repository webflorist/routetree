<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\Feature\Traits\UsesCachedRoutes;
use RouteTreeTests\Feature\Traits\UsesTestRoutes;
use RouteTreeTests\TestCase;

class CacheTest extends TestCase
{
    use UsesTestRoutes, UsesCachedRoutes;

    public function test_cache()
    {
        $this->assertComplexRegisteredRoutes();
    }

}