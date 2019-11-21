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
        $this->generateComplexTestRoutes();

        $response = $this->get('api/routetree/paths')->decodeResponseJson();
        //dd($response);
    }


}