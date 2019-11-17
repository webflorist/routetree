<?php

namespace Webflorist\RouteTree\Interfaces;

interface RouteParameterModelContract
{

    public static function getRouteParameterValues(string $locale=null, ?array $parameters=null);

}