<?php

namespace Webflorist\RouteTree\Interfaces;

interface ProvidesRoutePayload
{
    public static function getRoutePayload(string $payloadKey, array $parameters, string $locale, ?string $action);
}