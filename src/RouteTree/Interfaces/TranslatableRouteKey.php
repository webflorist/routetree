<?php

namespace Webflorist\RouteTree\Interfaces;

interface TranslatableRouteKey
{

    public static function getAllRouteKeys(string $locale=null, ?array $parameters=null);

    public static function translateRouteKey(string $value, string $toLocale, string $fromLocale);

}