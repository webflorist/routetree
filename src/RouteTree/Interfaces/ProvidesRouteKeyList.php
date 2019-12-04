<?php

namespace Webflorist\RouteTree\Interfaces;

interface ProvidesRouteKeyList
{
    public static function getRouteKeyList(string $locale = null, ?array $parameters = null): array;
}