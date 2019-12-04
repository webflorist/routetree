<?php

namespace RouteTreeTests\Feature;

use RouteTreeTests\Feature\Models\TestModelTranslatable;
use RouteTreeTests\Feature\Traits\UsesTestRoutes;
use RouteTreeTests\TestCase;

class ApiTest extends TestCase
{
    use UsesTestRoutes;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $this->config->set('routetree.api.enabled', true);
    }

    public function test_paths_index()
    {
        $this->generateComplexTestRoutes($this->routeTree);

        $response = $this->get('api/routetree/paths')->decodeResponseJson();
        //dd($response);
    }


}