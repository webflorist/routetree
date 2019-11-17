<?php

namespace Webflorist\RouteTree\Interfaces;

interface RouteKeyModelContract
{

    public static function getRouteKeyValues(string $locale=null, ?array $parameters=null);

    public static function translateRouteKeyValue(string $value, string $toLocale, string $fromLocale);

}