<?php

namespace Webflorist\RouteTree\Interfaces;

/**
 * Interface TranslatesRouteKey
 *
 * An Eloquent Model stated with a RouteParameter or RouteResource can implement this interface,
 * to provide functionality to translate a route key from one language to another.
 *
 * This can be used to e.g. to enable language-switching with parameter-routes.
 *
 * @package Webflorist\RouteTree
 */
interface TranslatesRouteKey
{
    /**
     * Should translate the route key $value from $fromLocale to $toLocale.
     *
     * @param string $value
     * @param string $toLocale
     * @param string $fromLocale
     * @return string
     */
    public static function translateRouteKey(string $value, string $toLocale, string $fromLocale): string;
}