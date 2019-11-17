<?php

namespace Webflorist\RouteTree\Interfaces;

interface RouteParameterModelContract
{

    public static function getRouteParameterValues(string $locale=null, ?array $parameters=null);

    public static function translateRouteParameterValue(string $value, string $toLocale, string $fromLocale);

}