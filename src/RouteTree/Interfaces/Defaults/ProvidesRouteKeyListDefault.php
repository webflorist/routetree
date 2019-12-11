<?php

namespace Webflorist\RouteTree\Interfaces\Defaults;

trait ProvidesRouteKeyListDefault
{
    public static function getRouteKeyList(string $locale = null, ?array $parameters = null): array
    {
        return self::pluck(
            (new self())->getRouteKeyName()
        )->toArray();
    }
}