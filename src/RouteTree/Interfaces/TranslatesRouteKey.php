<?php

namespace Webflorist\RouteTree\Interfaces;

interface TranslatesRouteKey
{
    public static function translateRouteKey(string $value, string $toLocale, string $fromLocale): string;
}