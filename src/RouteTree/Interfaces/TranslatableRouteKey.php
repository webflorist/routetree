<?php

namespace Webflorist\RouteTree\Interfaces;

interface TranslatableRouteKey
{

    public static function getAllRouteKeys(string $locale = null, ?array $parameters = null): ?array;

    public static function translateRouteKey(string $value, string $toLocale, string $fromLocale): string;

    public static function getRoutePayload(string $payloadKey, array $parameters, string $locale, ?string $action);

}